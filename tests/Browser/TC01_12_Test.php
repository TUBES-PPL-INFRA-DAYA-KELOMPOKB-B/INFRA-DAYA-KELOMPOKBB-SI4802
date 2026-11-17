<?php

namespace Tests\Browser;

use App\Models\User;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/**
 * TC01-12: U2 (Warga) aktif login. Akses URL Kelola Pengguna dan kirim request mutasi langsung.
 * Expected: Akses ditolak (403 Forbidden); data tetap aman.
 */
class TC01_12_Test extends DuskTestCase
{
    public function test_tc01_12(): void
    {
        $u2 = User::factory()->create([
            'name' => 'Siti Warga Biasa',
            'email' => 'siti.warga.'.uniqid().'@gmail.com',
            'role' => 'warga',
            'is_active' => true,
        ]);

        $this->browse(function (Browser $browser) use ($u2) {
            $browser->loginAs($u2)
                ->visit(route('admin.users.index'))
                ->assertSee('403')
                ->assertSee('Wewenang Tidak Mencukupi');
        });
    }
}
