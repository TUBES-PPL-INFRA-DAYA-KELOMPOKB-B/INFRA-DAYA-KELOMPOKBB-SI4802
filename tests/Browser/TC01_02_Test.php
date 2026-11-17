<?php

namespace Tests\Browser;

use App\Models\User;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/**
 * TC01-02: Cari pengguna berdasarkan nama dan email secara terpisah
 * Prasyarat: A1 login
 * Input: Siti; siti@gmail.com
 * Expected: Hanya akun U2 yang cocok ditampilkan
 */
class TC01_02_Test extends DuskTestCase
{
    public function test_tc01_02(): void
    {
        $admin = User::factory()->create([
            'email' => 'admin.tc0102.'.uniqid().'@dsdabm.bandung.go.id',
            'role' => 'admin',
            'is_active' => true,
        ]);

        $uniqueKey = 'Siti'.rand(1000, 9999);
        $u2 = User::factory()->create([
            'name' => $uniqueKey.' Aminah',
            'email' => strtolower($uniqueKey).'@gmail.com',
            'role' => 'warga',
            'is_active' => true,
        ]);

        $uOther = User::factory()->create([
            'name' => 'Bambang Lain',
            'email' => 'bambang.lain.'.uniqid().'@test.com',
            'role' => 'petugas',
            'is_active' => true,
        ]);

        $this->browse(function (Browser $browser) use ($admin, $u2, $uOther, $uniqueKey) {
            $browser->loginAs($admin);

            // 1. Cari berdasarkan Nama
            $browser->visit(route('admin.users.index'))
                ->type('@search-input', $uniqueKey)
                ->click('@search-btn')
                ->waitForText($u2->name)
                ->assertSee($u2->name)
                ->assertDontSee($uOther->name);

            // 2. Cari berdasarkan Email
            $browser->visit(route('admin.users.index'))
                ->type('@search-input', $u2->email)
                ->click('@search-btn')
                ->waitForText($u2->email)
                ->assertSee($u2->name)
                ->assertDontSee($uOther->name);
        });
    }
}
