<?php

namespace Tests\Browser;

use App\Models\User;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/**
 * TC01-05: Tambah pengguna dengan email yang sudah terdaftar
 * Input: Email: budi@bandung.go.id
 * Expected: Ditolak oleh sistem; pesan validasi email duplikat tampil
 */
class TC01_05_Test extends DuskTestCase
{
    public function test_tc01_05(): void
    {
        $admin = User::factory()->create([
            'email' => 'admin.tc0105.'.uniqid().'@dsdabm.bandung.go.id',
            'role' => 'admin',
            'is_active' => true,
        ]);

        $u1 = User::factory()->create([
            'name' => 'Budi Terdaftar',
            'email' => 'budi.'.uniqid().'@bandung.go.id',
            'role' => 'petugas',
        ]);

        $this->browse(function (Browser $browser) use ($admin, $u1) {
            $browser->loginAs($admin)
                ->visit(route('admin.users.create'))
                ->type('name', 'Budi Baru')
                ->type('email', $u1->email)
                ->type('phone', '081299998888')
                ->select('role', 'petugas')
                ->type('password', 'password123')
                ->type('password_confirmation', 'password123')
                ->click('@submit-btn')
                ->waitForText('Email tersebut sudah terdaftar')
                ->assertSee('Email tersebut sudah terdaftar');
        });
    }
}
