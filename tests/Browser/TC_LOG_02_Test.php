<?php

namespace Tests\Browser;

use App\Models\User;
use App\Models\UserLog;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/**
 * TC-LOG-02: A1 login; K2 nonaktif.
 * Langkah: Kelola K2 -> pilih Aktif -> Simpan -> periksa tabel log
 * Expected: Status K2 menjadi aktif; 1 baris log AKTIVASI_AKUN (account_activated) tercatat beserta waktu server.
 */
class TC_LOG_02_Test extends DuskTestCase
{
    public function test_tc_log_02(): void
    {
        $a1 = User::factory()->create([
            'name' => 'Admin Aktivator A1',
            'email' => 'admin.a1.act.'.uniqid().'@dsdabm.bandung.go.id',
            'role' => 'admin',
            'is_active' => true,
        ]);

        $k2 = User::factory()->create([
            'name' => 'Pegawai K2 Nonaktif',
            'email' => 'k2.nonaktif.'.uniqid().'@dsdabm.bandung.go.id',
            'role' => 'petugas',
            'is_active' => false,
            'deactivated_at' => now()->subDay(),
        ]);

        $this->browse(function (Browser $browser) use ($a1, $k2) {
            $browser->loginAs($a1)
                ->visit(route('admin.users.index', ['search' => $k2->name]))
                ->waitFor("@toggle-active-btn-{$k2->id}")
                ->click("@toggle-active-btn-{$k2->id}")
                ->waitForLocation('/admin/users')
                ->assertSee('diaktifkan')
                ->visit(route('admin.user-logs.index'))
                ->assertSee('Log Aktivitas Akun')
                ->assertSee($a1->name)
                ->assertSee($k2->name)
                ->assertSee('Aktivasi Akun');
        });

        $this->assertDatabaseHas('users', [
            'id' => $k2->id,
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('user_logs', [
            'actor_id' => $a1->id,
            'target_user_id' => $k2->id,
            'action' => 'account_activated',
        ]);
    }
}
