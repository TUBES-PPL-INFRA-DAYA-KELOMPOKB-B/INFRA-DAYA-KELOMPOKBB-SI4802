<?php

namespace Tests\Browser;

use App\Models\User;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/**
 * TC02-05: U1 (warga) login. U1 mengakses URL Dashboard SLA Pimpinan dan Panel Admin secara langsung.
 * Expected: Akses ditolak (403 Forbidden / dialihkan).
 */
class TC02_05_Test extends DuskTestCase
{
    public function test_tc02_05(): void
    {
        $u1 = User::factory()->create([
            'name' => 'Warga Akses Terlarang',
            'email' => 'warga.tc0205.'.uniqid().'@gmail.com',
            'role' => 'warga',
            'is_active' => true,
        ]);

        $this->browse(function (Browser $browser) use ($u1) {
            $browser->loginAs($u1);

            // Akses Panel Admin
            $browser->visit(route('admin.users.index'))
                ->assertSee('403')
                ->assertSee('Wewenang Tidak Mencukupi');

            // Akses Dashboard SLA Pimpinan
            $browser->visit(route('pimpinan.sla.index'))
                ->assertSee('403')
                ->assertSee('Wewenang Tidak Mencukupi');
        });
    }
}
