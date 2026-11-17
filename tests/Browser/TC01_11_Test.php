<?php

namespace Tests\Browser;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/**
 * TC01-11: U2 telah dinonaktifkan. Login menggunakan kredensial U2 yang benar.
 * Expected: Login ditolak; sistem menampilkan pesan bahwa akun telah dinonaktifkan.
 */
class TC01_11_Test extends DuskTestCase
{
    public function test_tc01_11(): void
    {
        $password = 'Password123!';
        $u2 = User::factory()->create([
            'name' => 'Siti Akun Nonaktif',
            'email' => 'siti.loginnondeact.'.uniqid().'@gmail.com',
            'password' => Hash::make($password),
            'role' => 'warga',
            'is_active' => false,
            'deactivated_at' => now(),
        ]);

        $this->browse(function (Browser $browser) use ($u2, $password) {
            $browser->visit(route('login'))
                ->type('email', $u2->email)
                ->type('password', $password)
                ->press('Masuk')
                ->waitForText('Akun Anda sedang dinonaktifkan oleh administrator.')
                ->assertSee('Akun Anda sedang dinonaktifkan oleh administrator.')
                ->assertPathIs('/login');
        });
    }
}
