<?php

namespace Tests\Browser;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/**
 * TC-LOG-03: K2 berstatus nonaktif. Login menggunakan kredensial K2 yang benar.
 * Expected: Login ditolak; tidak terbentuk sesi baru; pesan akun nonaktif tampil.
 */
class TC_LOG_03_Test extends DuskTestCase
{
    public function test_tc_log_03(): void
    {
        $rawPassword = 'PasswordK2123!';
        $k2 = User::factory()->create([
            'name' => 'Petugas K2 Nonaktif Login',
            'email' => 'k2.loginblock.'.uniqid().'@dsdabm.bandung.go.id',
            'password' => Hash::make($rawPassword),
            'role' => 'petugas',
            'is_active' => false,
            'deactivated_at' => now(),
        ]);

        $this->browse(function (Browser $browser) use ($k2, $rawPassword) {
            $browser->visit(route('login'))
                ->type('email', $k2->email)
                ->type('password', $rawPassword)
                ->press('Masuk')
                ->waitForText('Akun Anda sedang dinonaktifkan oleh administrator.')
                ->assertSee('Akun Anda sedang dinonaktifkan oleh administrator.')
                ->assertPathIs('/login');
        });
    }
}
