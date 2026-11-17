<?php

namespace Tests\Browser;

use App\Models\User;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/**
 * TC02-03: A1 login; U1 tersedia. Kirim perubahan role kosong atau nilai RX melalui request langsung.
 * Expected: Ditolak oleh server (422); role U1 tetap seperti semula.
 */
class TC02_03_Test extends DuskTestCase
{
    public function test_tc02_03(): void
    {
        $admin = User::factory()->create([
            'email' => 'admin.tc0203.'.uniqid().'@dsdabm.bandung.go.id',
            'role' => 'admin',
            'is_active' => true,
        ]);

        $u1 = User::factory()->create([
            'name' => 'Warga Asli U1',
            'email' => 'warga.asli.'.uniqid().'@gmail.com',
            'role' => 'warga',
            'is_active' => true,
        ]);

        $this->browse(function (Browser $browser) use ($admin, $u1) {
            $browser->loginAs($admin)
                ->visit(route('admin.users.edit', $u1->id))
                // Ubah nilai select role menjadi invalid 'RX' via script
                ->script("document.querySelector('select[name=\"role\"]').innerHTML += '<option value=\"RX\" selected>Invalid RX</option>';");

            $browser->click('@submit-btn')
                ->waitForLocation("/admin/users/{$u1->id}/edit")
                ->assertPathIs("/admin/users/{$u1->id}/edit")
                ->assertSee('Role yang dipilih tidak valid.');
        });

        // Verifikasi role U1 tetap 'warga' di database
        $this->assertDatabaseHas('users', [
            'id' => $u1->id,
            'role' => 'warga',
        ]);
    }
}
