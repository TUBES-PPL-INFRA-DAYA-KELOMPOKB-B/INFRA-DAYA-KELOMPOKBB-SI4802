<?php

namespace Tests\Browser;

use App\Models\User;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/**
 * TC01-04: Klik Tambah Pengguna -> isi seluruh data valid -> Simpan
 * Input: Nama: Rudi, Email: rudi@bandung.go.id, Telp: 08123456789
 * Expected: Akun baru tersimpan di database dengan status aktif dan tampil pada daftar
 */
class TC01_04_Test extends DuskTestCase
{
    public function test_tc01_04(): void
    {
        $admin = User::factory()->create([
            'email' => 'admin.tc0104.'.uniqid().'@dsdabm.bandung.go.id',
            'role' => 'admin',
            'is_active' => true,
        ]);

        $uniqueEmail = 'rudi.'.uniqid().'@bandung.go.id';

        $this->browse(function (Browser $browser) use ($admin, $uniqueEmail) {
            $browser->loginAs($admin)
                ->visit(route('admin.users.create'))
                ->type('name', 'Rudi')
                ->type('email', $uniqueEmail)
                ->type('phone', '08123456789')
                ->select('role', 'petugas')
                ->type('password', 'password123')
                ->type('password_confirmation', 'password123')
                ->click('@submit-btn')
                ->waitForLocation('/admin/users')
                ->assertSee('berhasil ditambahkan')
                ->type('@search-input', 'Rudi')
                ->click('@search-btn')
                ->waitForText('Rudi')
                ->assertSee('Rudi')
                ->assertSee($uniqueEmail);
        });

        $this->assertDatabaseHas('users', [
            'name' => 'Rudi',
            'email' => $uniqueEmail,
            'is_active' => true,
        ]);
    }
}
