<?php

namespace Tests\Browser;

use App\Models\User;
use App\Models\UserLog;
use App\Services\UserLogService;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/**
 * TC-LOG-04: A1 login; data log tersedia.
 * Langkah: Buka halaman Log Aktivitas -> filter berdasarkan jenis aksi (account_activated)
 * Expected: Menampilkan daftar log aktivasi akun secara kronologis.
 */
class TC_LOG_04_Test extends DuskTestCase
{
    public function test_tc_log_04(): void
    {
        $a1 = User::factory()->create([
            'email' => 'admin.tclog04.'.uniqid().'@dsdabm.bandung.go.id',
            'role' => 'admin',
            'is_active' => true,
        ]);

        $u1 = User::factory()->create([
            'name' => 'Target Aktivasi Akun',
            'email' => 'target.act.'.uniqid().'@gmail.com',
        ]);

        $u2 = User::factory()->create([
            'name' => 'Target Peran Diubah',
            'email' => 'target.role.'.uniqid().'@gmail.com',
        ]);

        $logService = app(UserLogService::class);
        $logService->log('account_activated', $u1->id, ['is_active' => false], ['is_active' => true], 'Pengaktifan kembali akun', $a1->id);
        $logService->log('role_changed', $u2->id, ['role' => 'warga'], ['role' => 'petugas'], 'Promosi staf', $a1->id);

        $this->browse(function (Browser $browser) use ($a1, $u1, $u2) {
            $browser->loginAs($a1)
                ->visit(route('admin.user-logs.index'))
                ->assertSee('Log Aktivitas Akun')
                ->select('@log-action-select', 'account_activated')
                ->click('@log-filter-btn')
                ->waitForText('Aktivasi Akun')
                ->assertSee('Aktivasi Akun')
                ->assertSee($u1->name)
                ->assertDontSee('Promosi staf');
        });
    }
}
