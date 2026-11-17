<?php

namespace Tests\Browser;

use App\Models\User;
use App\Models\UserLog;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/**
 * TC-LOG-01: A1 login; K1 aktif.
 * Langkah: Kelola K1 -> pilih Nonaktif -> Simpan -> periksa tabel log
 * Expected: Status K1 menjadi nonaktif; 1 baris log NONAKTIF_AKUN (account_deactivated) dengan pelaku A1 tercatat.
 */
class TC_LOG_01_Test extends DuskTestCase
{
    public function test_tc_log_01(): void
    {
        $a1 = User::factory()->create([
            'name' => 'Admin Aktor A1',
            'email' => 'admin.a1.'.uniqid().'@dsdabm.bandung.go.id',
            'role' => 'admin',
            'is_active' => true,
        ]);

        $k1 = User::factory()->create([
            'name' => 'Pegawai K1 Aktif',
            'email' => 'k1.aktif.'.uniqid().'@dsdabm.bandung.go.id',
            'role' => 'petugas',
            'is_active' => true,
        ]);

        $this->browse(function (Browser $browser) use ($a1, $k1) {
            $browser->loginAs($a1)
                ->visit(route('admin.users.index', ['search' => $k1->name]))
                ->waitFor("@toggle-active-btn-{$k1->id}")
                ->click("@toggle-active-btn-{$k1->id}")
                ->waitForLocation('/admin/users')
                ->assertSee('dinonaktifkan')
                ->visit(route('admin.user-logs.index'))
                ->assertSee('Log Aktivitas Akun')
                ->assertSee($a1->name)
                ->assertSee($k1->name)
                ->assertSee('Nonaktivasi Akun');
        });

        $this->assertDatabaseHas('users', [
            'id' => $k1->id,
            'is_active' => false,
        ]);

        $this->assertDatabaseHas('user_logs', [
            'actor_id' => $a1->id,
            'target_user_id' => $k1->id,
            'action' => 'account_deactivated',
        ]);
    }
}
