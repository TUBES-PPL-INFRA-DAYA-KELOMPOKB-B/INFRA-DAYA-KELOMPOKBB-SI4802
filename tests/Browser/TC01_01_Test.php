<?php

namespace Tests\Browser;

use App\Models\User;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/**
 * TC01-01: Buka menu Kelola Pengguna
 * Prasyarat: A1 login; U1, U2, U3 tersedia
 * Expected: Daftar akun U1, U2, dan U3 tampil beserta status aktifnya
 */
class TC01_01_Test extends DuskTestCase
{
    public function test_tc01_01(): void
    {
        $admin = User::factory()->create([
            'email' => 'admin.tc0101.'.uniqid().'@dsdabm.bandung.go.id',
            'role' => 'admin',
            'is_active' => true,
        ]);

        $u1 = User::factory()->create([
            'name' => 'AAA Budi Santoso',
            'email' => 'budi.tc0101.'.uniqid().'@bandung.go.id',
            'role' => 'petugas',
            'is_active' => true,
        ]);

        $u2 = User::factory()->create([
            'name' => 'AAA Siti Aminah',
            'email' => 'siti.tc0101.'.uniqid().'@gmail.com',
            'role' => 'warga',
            'is_active' => true,
        ]);

        $u3 = User::factory()->create([
            'name' => 'AAA Ujang Teknisi',
            'email' => 'ujang.tc0101.'.uniqid().'@dsdabm.bandung.go.id',
            'role' => 'petugas',
            'is_active' => true,
        ]);

        $this->browse(function (Browser $browser) use ($admin, $u1, $u2, $u3) {
            $browser->loginAs($admin)
                ->visit(route('admin.users.index'))
                ->waitForText('Kelola Pengguna')
                ->assertSee('Kelola Pengguna')
                ->assertSee($u1->name)
                ->assertSee($u2->name)
                ->assertSee($u3->name)
                ->assertSee('Aktif');
        });
    }
}
