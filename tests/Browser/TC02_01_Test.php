<?php

namespace Tests\Browser;

use App\Models\User;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/**
 * TC02-01: A1 login. Pilih filter role Petugas Lapangan pada daftar pengguna.
 * Expected: Daftar hanya menampilkan akun dengan role Petugas Lapangan.
 */
class TC02_01_Test extends DuskTestCase
{
    public function test_tc02_01(): void
    {
        $admin = User::factory()->create([
            'email' => 'admin.tc0201.'.uniqid().'@dsdabm.bandung.go.id',
            'role' => 'admin',
            'is_active' => true,
        ]);

        $u2 = User::factory()->create([
            'name' => 'AAA Petugas Khusus U2',
            'email' => 'petugas.u2.'.uniqid().'@dsdabm.bandung.go.id',
            'role' => 'petugas',
            'is_active' => true,
        ]);

        $warga = User::factory()->create([
            'name' => 'Warga Non Petugas',
            'email' => 'warga.tc0201.'.uniqid().'@gmail.com',
            'role' => 'warga',
            'is_active' => true,
        ]);

        $this->browse(function (Browser $browser) use ($admin, $u2, $warga) {
            $browser->loginAs($admin)
                ->visit(route('admin.users.index'))
                ->select('@role-select', 'petugas')
                ->click('@search-btn')
                ->waitForLocation('/admin/users')
                ->assertSee($u2->name)
                ->assertSee('Petugas Lapangan')
                ->assertDontSee($warga->name);
        });
    }
}
