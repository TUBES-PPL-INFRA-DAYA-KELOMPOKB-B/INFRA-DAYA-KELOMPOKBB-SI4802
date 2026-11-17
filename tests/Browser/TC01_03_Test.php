<?php

namespace Tests\Browser;

use App\Models\User;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/**
 * TC01-03: Cari kata kunci tanpa kecocokan
 * Prasyarat: A1 login
 * Input: TidakAdaUserXYZ
 * Expected: Pesan data tidak ditemukan tampil; tidak terjadi error
 */
class TC01_03_Test extends DuskTestCase
{
    public function test_tc01_03(): void
    {
        $admin = User::factory()->create([
            'email' => 'admin.tc0103.'.uniqid().'@dsdabm.bandung.go.id',
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->browse(function (Browser $browser) use ($admin) {
            $browser->loginAs($admin)
                ->visit(route('admin.users.index'))
                ->type('@search-input', 'TidakAdaUserXYZ')
                ->click('@search-btn')
                ->waitForText('Tidak ada data pengguna ditemukan.')
                ->assertSee('Tidak ada data pengguna ditemukan.')
                ->assertDontSee('500 Server Error');
        });
    }
}
