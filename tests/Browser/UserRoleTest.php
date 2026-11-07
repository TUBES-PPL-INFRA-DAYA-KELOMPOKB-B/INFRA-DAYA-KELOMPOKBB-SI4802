<?php

namespace Tests\Browser;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/**
 * PBI-5: Pengujian E2E Mengatur Role Akses.
 * Mengacu pada PRD Skenario T-06, T-07, T-08, T-09, dan T-12.
 * PIC: Muhammad Daffa Fachrurozi (102022400151).
 */
class UserRoleTest extends DuskTestCase
{
    use DatabaseMigrations;

    private function loginAsAdmin(Browser $browser): User
    {
        $admin = User::factory()->create([
            'email' => 'admin.role.'.uniqid().'@dsdabm.bandung.go.id',
            'role' => 'admin',
            'is_active' => true,
        ]);
        $browser->loginAs($admin);

        return $admin;
    }

    /**
     * T-06: Admin mengubah role user -> Role berubah dan akses menu ikut berubah.
     */
    public function test_t06_admin_changes_user_role_and_menu_updates(): void
    {
        $targetUser = User::factory()->create([
            'name' => 'Staf Mutasi Jabatan',
            'email' => 'staf.mutasi.'.uniqid().'@dsdabm.bandung.go.id',
            'role' => 'petugas',
            'is_active' => true,
        ]);

        $this->browse(function (Browser $browser) use ($targetUser) {
            // 1. Admin masuk dan mengubah role user via form edit
            $this->loginAsAdmin($browser);

            $browser->visit(route('admin.users.edit', $targetUser->id))
                ->select('role', 'pimpinan')
                ->click('@submit-btn')
                ->waitForLocation('/admin/users')
                ->assertSee('berhasil diperbarui');

            // 2. Target user login dengan akunnya
            $browser->loginAs($targetUser->fresh())
                ->visit(route('dashboard'))
                ->assertSee('Dashboard Eksekutif')
                ->assertSee('Role: PIMPINAN');

            // 3. User dapat membuka rute pimpinan, tetapi ditolak saat membuka rute petugas sebelumnya
            $browser->visit(route('pimpinan.sla.index'))
                ->assertPathIs('/pimpinan/sla');

            $browser->visit(route('petugas.tugas.index'))
                ->assertSee('403')
                ->assertSee('Wewenang Tidak Mencukupi');
        });
    }

    /**
     * T-07: Warga membuka URL modul user langsung -> Ditolak (HTTP 403).
     */
    public function test_t07_warga_cannot_access_user_module_url_directly(): void
    {
        $warga = User::factory()->create([
            'email' => 'warga.cekurl.'.uniqid().'@bandung.go.id',
            'role' => 'warga',
            'is_active' => true,
        ]);

        $this->browse(function (Browser $browser) use ($warga) {
            $browser->loginAs($warga)
                ->visit(route('admin.users.index'))
                ->assertSee('403')
                ->assertSee('Wewenang Tidak Mencukupi');
        });
    }

    /**
     * T-08: Petugas dan Pimpinan membuka URL modul user langsung -> Ditolak (HTTP 403).
     */
    public function test_t08_petugas_and_pimpinan_cannot_access_user_module_directly(): void
    {
        $petugas = User::factory()->create([
            'email' => 'petugas.cekurl.'.uniqid().'@dsdabm.bandung.go.id',
            'role' => 'petugas',
            'is_active' => true,
        ]);

        $pimpinan = User::factory()->create([
            'email' => 'pimpinan.cekurl.'.uniqid().'@dsdabm.bandung.go.id',
            'role' => 'pimpinan',
            'is_active' => true,
        ]);

        $this->browse(function (Browser $browser) use ($petugas, $pimpinan) {
            // Uji Petugas
            $browser->loginAs($petugas)
                ->visit(route('admin.users.index'))
                ->assertSee('403')
                ->assertSee('Wewenang Tidak Mencukupi');

            // Uji Pimpinan
            $browser->loginAs($pimpinan)
                ->visit(route('admin.users.index'))
                ->assertSee('403')
                ->assertSee('Wewenang Tidak Mencukupi');
        });
    }

    /**
     * T-09: Pengguna belum login membuka modul user -> Diarahkan ke halaman login.
     */
    public function test_t09_unauthenticated_user_redirected_to_login(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(route('admin.users.index'))
                ->waitForLocation('/login')
                ->assertPathIs('/login');
        });
    }

    /**
     * T-12: Penurunan role admin aktif terakhir -> Ditolak dengan pesan.
     * (Catatan: Skenario nonaktivasi akun pada T-12 diuji saat integrasi PBI-6).
     */
    public function test_t12_demoting_the_last_active_admin_is_rejected(): void
    {
        $this->browse(function (Browser $browser) {
            // Pastikan hanya ada 1 admin yang dibuat
            $admin = $this->loginAsAdmin($browser);

            // Admin mencoba menurunkan rolenya sendiri menjadi petugas lewat form
            $browser->visit(route('admin.users.edit', $admin->id))
                ->select('role', 'petugas')
                ->click('@submit-btn')
                ->waitForLocation("/admin/users/{$admin->id}/edit")
                ->assertSee('Anda tidak dapat mengubah role akun Anda sendiri');
        });
    }
}
