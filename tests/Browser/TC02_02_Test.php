<?php

namespace Tests\Browser;

use App\Models\User;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/**
 * TC02-02: A1 login; U1 (warga) tersedia.
 * Langkah: Kelola U1 -> ubah role menjadi petugas -> Simpan -> muat ulang
 * Expected: Role U1 tersimpan sebagai petugas di UI dan database.
 */
class TC02_02_Test extends DuskTestCase
{
    public function test_tc02_02(): void
    {
        $admin = User::factory()->create([
            'email' => 'admin.tc0202.'.uniqid().'@dsdabm.bandung.go.id',
            'role' => 'admin',
            'is_active' => true,
        ]);

        $u1 = User::factory()->create([
            'name' => 'Warga Promosi U1',
            'email' => 'warga.promosi.'.uniqid().'@gmail.com',
            'role' => 'warga',
            'is_active' => true,
        ]);

        $this->browse(function (Browser $browser) use ($admin, $u1) {
            $browser->loginAs($admin)
                ->visit(route('admin.users.edit', $u1->id))
                ->select('role', 'petugas')
                ->click('@submit-btn')
                ->waitForLocation('/admin/users')
                ->assertSee('berhasil diperbarui')
                ->visit(route('admin.users.index', ['search' => $u1->name]))
                ->waitForText($u1->name)
                ->assertSee($u1->name)
                ->assertSee('Petugas Lapangan');
        });

        $this->assertDatabaseHas('users', [
            'id' => $u1->id,
            'role' => 'petugas',
        ]);
    }
}
