<?php

namespace Tests\Browser;

use App\Models\User;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/**
 * TC02-04: A1 login. A1 mencoba mengubah role akunnya sendiri menjadi warga.
 * Expected: Ditolak; sistem mencegah penurunan role diri sendiri.
 */
class TC02_04_Test extends DuskTestCase
{
    public function test_tc02_04(): void
    {
        $admin = User::factory()->create([
            'email' => 'admin.tc0204.'.uniqid().'@dsdabm.bandung.go.id',
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->browse(function (Browser $browser) use ($admin) {
            $browser->loginAs($admin)
                ->visit(route('admin.users.edit', $admin->id))
                ->select('role', 'warga')
                ->click('@submit-btn')
                ->waitForLocation("/admin/users/{$admin->id}/edit")
                ->assertSee('Anda tidak dapat mengubah role akun Anda sendiri');
        });

        $this->assertDatabaseHas('users', [
            'id' => $admin->id,
            'role' => 'admin',
        ]);
    }
}
