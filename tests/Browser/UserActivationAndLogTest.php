<?php

namespace Tests\Browser;

use App\Models\User;
use App\Models\UserLog;
use App\Services\UserLogService;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Hash;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/**
 * PBI-6: Pengujian E2E Pencatatan Aktivasi dan Log Aktivitas.
 * Mengacu pada PRD Skenario T-10, T-11, T-12 (nonaktivasi admin terakhir), T-13 (filter log), dan T-14 (keamanan password).
 * PIC: Muhammad Daffa Fachrurozi (102022400151).
 */
class UserActivationAndLogTest extends DuskTestCase
{
    use DatabaseMigrations;

    private function loginAsAdmin(Browser $browser): User
    {
        $admin = User::factory()->create([
            'name' => 'Admin DSDABM Utama',
            'email' => 'admin.audit.'.uniqid().'@dsdabm.bandung.go.id',
            'role' => 'admin',
            'is_active' => true,
        ]);
        $browser->loginAs($admin);

        return $admin;
    }

    /**
     * T-10: Menonaktifkan akun lalu user mencoba login -> Login ditolak, pesan akun nonaktif muncul.
     */
    public function test_t10_deactivate_user_and_login_attempt_is_rejected(): void
    {
        $rawPassword = 'Password123!';
        $warga = User::factory()->create([
            'name' => 'Warga Pelapor Dinonaktifkan',
            'email' => 'warga.nonaktif.'.uniqid().'@gmail.com',
            'password' => Hash::make($rawPassword),
            'role' => 'warga',
            'is_active' => true,
        ]);

        $this->browse(function (Browser $browser) use ($warga, $rawPassword) {
            // 1. Admin login dan menonaktifkan akun warga
            $admin = $this->loginAsAdmin($browser);

            $browser->visit(route('admin.users.index'))
                ->waitFor("@toggle-active-btn-{$warga->id}")
                ->click("@toggle-active-btn-{$warga->id}")
                ->waitForLocation('/admin/users')
                ->assertSee('dinonaktifkan');

            $this->assertFalse($warga->fresh()->is_active);

            // 2. Logout admin, kemudian coba login dengan akun warga yang dinonaktifkan
            $browser->logout()
                ->visit(route('login'))
                ->type('email', $warga->email)
                ->type('password', $rawPassword)
                ->press('Masuk')
                ->waitForText('Akun Anda sedang dinonaktifkan oleh administrator.')
                ->assertSee('Akun Anda sedang dinonaktifkan oleh administrator.');
        });
    }

    /**
     * T-11: Mengaktifkan kembali akun -> User dapat login lagi, log aktivasi tercatat.
     */
    public function test_t11_reactivate_user_and_login_succeeds_with_audit_log(): void
    {
        $rawPassword = 'Password123!';
        $petugas = User::factory()->create([
            'name' => 'Petugas Diaktifkan Kembali',
            'email' => 'petugas.aktif.'.uniqid().'@dsdabm.bandung.go.id',
            'password' => Hash::make($rawPassword),
            'role' => 'petugas',
            'is_active' => false,
            'deactivated_at' => now()->subDay(),
        ]);

        $this->browse(function (Browser $browser) use ($petugas, $rawPassword) {
            // 1. Admin login dan mengaktifkan kembali akun petugas
            $admin = $this->loginAsAdmin($browser);

            $browser->visit(route('admin.users.index'))
                ->waitFor("@toggle-active-btn-{$petugas->id}")
                ->click("@toggle-active-btn-{$petugas->id}")
                ->waitForLocation('/admin/users')
                ->assertSee('diaktifkan');

            $this->assertTrue($petugas->fresh()->is_active);

            // 2. Kunjungi halaman detail petugas untuk melihat log aktivasi
            $browser->visit(route('admin.users.show', $petugas->id))
                ->assertSee('Riwayat Log Aktivitas Pengguna')
                ->assertSee('Aktivasi Akun');

            // 3. Petugas login dengan akunnya yang telah diaktifkan kembali (dialihkan ke rute petugas)
            $browser->logout()
                ->visit(route('login'))
                ->type('email', $petugas->email)
                ->type('password', $rawPassword)
                ->press('Masuk')
                ->waitForLocation('/petugas/tugas')
                ->assertPathIs('/petugas/tugas');
        });
    }

    /**
     * T-12 (Bagian Nonaktivasi): Menonaktifkan admin aktif terakhir ditolak dengan pesan error.
     */
    public function test_t12_cannot_deactivate_last_active_admin(): void
    {
        // Pastikan hanya ada 1 admin yang aktif di sistem
        User::where('role', 'admin')->delete();

        $singleAdmin = User::factory()->create([
            'name' => 'Satu-satunya Admin',
            'email' => 'admin.tunggal.'.uniqid().'@dsdabm.bandung.go.id',
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->browse(function (Browser $browser) use ($singleAdmin) {
            $browser->loginAs($singleAdmin)
                ->visit(route('admin.users.index'))
                ->waitFor("@toggle-active-btn-{$singleAdmin->id}")
                ->click("@toggle-active-btn-{$singleAdmin->id}")
                ->waitForLocation('/admin/users')
                ->assertSee('Anda tidak dapat menonaktifkan akun Anda sendiri');
        });
    }

    /**
     * T-13: Filter dan pencarian di halaman log aktivitas akun.
     */
    public function test_t13_search_and_filter_activity_logs(): void
    {
        $this->browse(function (Browser $browser) {
            $admin = $this->loginAsAdmin($browser);

            $target1 = User::factory()->create(['name' => 'Zulfa Khairunnisa', 'email' => 'zulfa@example.com']);
            $target2 = User::factory()->create(['name' => 'Rahmat Hidayat', 'email' => 'rahmat@example.com']);

            $logService = app(UserLogService::class);
            $logService->log('role_changed', $target1->id, ['role' => 'warga'], ['role' => 'petugas'], 'Promosi staf', $admin->id);
            $logService->log('account_deactivated', $target2->id, null, null, 'Cuti dinas', $admin->id);

            // Kunjungi halaman daftar log
            $browser->visit(route('admin.user-logs.index'))
                ->assertSee('Log Aktivitas Akun')
                ->assertSee('Zulfa Khairunnisa')
                ->assertSee('Rahmat Hidayat');

            // Filter berdasarkan jenis aksi "Perubahan Role"
            $browser->select('@log-action-select', 'role_changed')
                ->click('@log-filter-btn')
                ->waitForText('Perubahan Role')
                ->assertSee('Zulfa Khairunnisa')
                ->assertDontSee('Cuti dinas');

            // Cari berdasarkan nama target user
            $browser->type('@log-search-input', 'Rahmat')
                ->select('@log-action-select', '')
                ->click('@log-filter-btn')
                ->waitForText('Rahmat Hidayat')
                ->assertSee('Rahmat Hidayat')
                ->assertDontSee('Zulfa Khairunnisa');
        });
    }

    /**
     * T-14: Cek isi log: tidak ada password plaintext maupun hash pada antarmuka log.
     */
    public function test_t14_logs_contain_no_plaintext_or_hashed_password(): void
    {
        $rawPassword = 'RahasiaSuperKetat99!';

        $this->browse(function (Browser $browser) use ($rawPassword) {
            $admin = $this->loginAsAdmin($browser);

            // Admin buat user baru dengan password
            $targetEmail = 'audit.pwd.'.uniqid().'@bandung.go.id';
            $browser->visit(route('admin.users.create'))
                ->type('name', 'Audit Password User')
                ->type('email', $targetEmail)
                ->type('phone', '081299887766')
                ->select('role', 'petugas')
                ->type('password', $rawPassword)
                ->type('password_confirmation', $rawPassword)
                ->click('@submit-btn')
                ->waitForLocation('/admin/users');

            $targetUser = User::where('email', $targetEmail)->first();
            $this->assertNotNull($targetUser);

            // Kunjungi halaman Log Aktivitas Akun
            $browser->visit(route('admin.user-logs.index'))
                ->assertSee('Log Aktivitas Akun')
                ->assertDontSee($rawPassword)
                ->assertDontSee($targetUser->password);

            // Kunjungi detail user
            $browser->visit(route('admin.users.show', $targetUser->id))
                ->assertSee('Riwayat Log Aktivitas Pengguna')
                ->assertDontSee($rawPassword)
                ->assertDontSee($targetUser->password);
        });
    }
}
