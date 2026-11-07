<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\UserService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Pengujian Fitur PBI-5: Mengatur Role Akses.
 * Mengacu pada Acceptance Criteria PRD AC 5.1 - 5.6 & Matriks Akses.
 * PIC: Muhammad Daffa Fachrurozi (102022400151).
 */
class UserRoleManagementTest extends TestCase
{
    use RefreshDatabase;

    private function createAdmin(): User
    {
        return User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);
    }

    /**
     * AC 5.1: Sistem hanya menerima 4 role baku (admin, petugas, pimpinan, warga).
     */
    public function test_system_only_accepts_four_standard_roles(): void
    {
        $admin = $this->createAdmin();
        $targetUser = User::factory()->create(['role' => 'warga']);

        // 1. Role valid: petugas
        $responseValid = $this->actingAs($admin)->patch(route('admin.users.update-role', $targetUser->id), [
            'role' => 'petugas',
        ]);
        $responseValid->assertSessionDoesntHaveErrors('role');
        $this->assertEquals('petugas', $targetUser->fresh()->role);

        // 2. Role tidak valid: superadmin / manager
        $responseInvalid = $this->actingAs($admin)->patch(route('admin.users.update-role', $targetUser->id), [
            'role' => 'superadmin',
        ]);
        $responseInvalid->assertSessionHasErrors('role');
    }

    /**
     * AC 5.2: Perubahan role berlaku pada permintaan berikutnya tanpa perlu re-login / sesi baru.
     */
    public function test_role_change_takes_effect_on_subsequent_request_without_new_session(): void
    {
        $admin = $this->createAdmin();

        // Buat user dengan role 'petugas'
        $petugas = User::factory()->create([
            'role' => 'petugas',
            'is_active' => true,
        ]);

        // 1. Petugas login dan mencoba membuka area admin -> DITOLAK 403
        $this->actingAs($petugas)->get(route('admin.users.index'))->assertForbidden();

        // 2. Admin mempromosikan user tersebut menjadi 'admin'
        $this->actingAs($admin)->patch(route('admin.users.update-role', $petugas->id), [
            'role' => 'admin',
        ]);
        $this->assertEquals('admin', $petugas->fresh()->role);

        // 3. User yang sama mengirim permintaan berikutnya ke area admin -> DITERIMA 200 OK
        $this->actingAs($petugas->fresh())->get(route('admin.users.index'))->assertOk();

        // 4. Admin menurunkan role user tersebut menjadi 'warga'
        $this->actingAs($admin)->patch(route('admin.users.update-role', $petugas->id), [
            'role' => 'warga',
        ]);

        // 5. Permintaan berikutnya langsung ditolak 403 Forbidden
        $this->actingAs($petugas->fresh())->get(route('admin.users.index'))->assertForbidden();
    }

    /**
     * AC 5.3: Middleware menolak akses pengguna tanpa kewenangan dengan HTTP 403 ramah.
     */
    public function test_unauthorized_users_receive_http_403_forbidden(): void
    {
        $warga = User::factory()->create(['role' => 'warga', 'is_active' => true]);
        $petugas = User::factory()->create(['role' => 'petugas', 'is_active' => true]);
        $pimpinan = User::factory()->create(['role' => 'pimpinan', 'is_active' => true]);

        // Warga membuka modul admin -> 403
        $this->actingAs($warga)->get(route('admin.users.index'))
            ->assertStatus(403)
            ->assertSee('Akses Ditolak');

        // Petugas membuka modul admin -> 403
        $this->actingAs($petugas)->get(route('admin.users.index'))
            ->assertStatus(403)
            ->assertSee('Akses Ditolak');

        // Pimpinan membuka modul admin user -> 403
        $this->actingAs($pimpinan)->get(route('admin.users.index'))
            ->assertStatus(403)
            ->assertSee('Akses Ditolak');
    }

    /**
     * AC 5.3: Pengguna belum login diarahkan ke halaman login.
     */
    public function test_unauthenticated_users_are_redirected_to_login(): void
    {
        $this->get(route('admin.users.index'))
            ->assertRedirect(route('login'));

        $this->get(route('petugas.tugas.index'))
            ->assertRedirect(route('login'));

        $this->get(route('warga.laporan.saya'))
            ->assertRedirect(route('login'));
    }

    /**
     * AC 5.5: Perlindungan Admin aktif terakhir — penurunan peran ditolak.
     */
    public function test_system_prevents_demoting_the_last_active_admin(): void
    {
        // Pastikan hanya ada 1 admin aktif di sistem
        User::where('role', 'admin')->delete();

        $soleAdmin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        // Buat admin kedua sementara untuk melakukan request perubahan pada soleAdmin
        $adminExecutor = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        // Hapus adminExecutor agar soleAdmin benar-benar menjadi satu-satunya admin aktif
        $adminExecutor->delete();

        // Hubungi service langsung untuk menguji logika bisnis perlindungan
        $this->assertTrue($soleAdmin->isLastActiveAdmin());

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Tidak dapat menurunkan role Admin aktif terakhir');

        app(UserService::class)->updateUserRole($soleAdmin->id, 'petugas');
    }

    /**
     * AC 5.5: Perlindungan Admin aktif terakhir — penonaktifan akun ditolak.
     */
    public function test_system_prevents_deactivating_the_last_active_admin(): void
    {
        User::where('role', 'admin')->delete();

        $soleAdmin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->assertTrue($soleAdmin->isLastActiveAdmin());

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Tidak dapat menonaktifkan Admin aktif terakhir');

        app(UserService::class)->toggleUserStatus($soleAdmin->id);
    }

    /**
     * AC 5.6: Admin tidak dapat mengubah role akunnya sendiri.
     */
    public function test_admin_cannot_change_own_role(): void
    {
        $admin = $this->createAdmin();

        // Buat admin kedua agar tidak terhalang oleh aturan admin terakhir
        $secondAdmin = $this->createAdmin();

        // 1. Coba ubah role akun sendiri via aksi khusus update-role
        $responseAction = $this->actingAs($admin)->patch(route('admin.users.update-role', $admin->id), [
            'role' => 'petugas',
        ]);

        $responseAction->assertRedirect();
        $responseAction->assertSessionHas('error', 'Anda tidak dapat mengubah role akun Anda sendiri.');
        $this->assertEquals('admin', $admin->fresh()->role);

        // 2. Coba ubah role akun sendiri via form edit update
        $responseUpdate = $this->actingAs($admin)->put(route('admin.users.update', $admin->id), [
            'name' => $admin->name,
            'email' => $admin->email,
            'role' => 'warga',
        ]);

        $responseUpdate->assertRedirect(route('admin.users.edit', $admin->id));
        $responseUpdate->assertSessionHas('error', 'Anda tidak dapat mengubah role akun Anda sendiri.');
        $this->assertEquals('admin', $admin->fresh()->role);
    }

    /**
     * Matriks Akses PRD Bagian 6:
     * - Admin: Master data, verifikasi, disposisi, manajemen user.
     * - Petugas: Daftar tugas, mulai/selesai pengerjaan.
     * - Pimpinan: Dashboard SLA, heatmap peta, ekspor eksekutif.
     * - Warga: Lapor kerusakan, riwayat laporan.
     */
    public function test_access_matrix_per_role(): void
    {
        $admin = $this->createAdmin();
        $petugas = User::factory()->create(['role' => 'petugas', 'is_active' => true]);
        $pimpinan = User::factory()->create(['role' => 'pimpinan', 'is_active' => true]);
        $warga = User::factory()->create(['role' => 'warga', 'is_active' => true]);

        // Verifikasi rute Admin
        $this->actingAs($admin)->get(route('admin.verifikasi.index'))->assertOk();
        $this->actingAs($petugas)->get(route('admin.verifikasi.index'))->assertForbidden();
        $this->actingAs($warga)->get(route('admin.verifikasi.index'))->assertForbidden();

        // Verifikasi rute Petugas
        $this->actingAs($petugas)->get(route('petugas.tugas.index'))->assertOk();
        $this->actingAs($warga)->get(route('petugas.tugas.index'))->assertForbidden();
        $this->actingAs($admin)->get(route('petugas.tugas.index'))->assertForbidden();

        // Verifikasi rute Pimpinan & Admin
        $this->actingAs($pimpinan)->get(route('pimpinan.sla.index'))->assertOk();
        $this->actingAs($admin)->get(route('pimpinan.sla.index'))->assertOk();
        $this->actingAs($warga)->get(route('pimpinan.sla.index'))->assertForbidden();
        $this->actingAs($petugas)->get(route('pimpinan.sla.index'))->assertForbidden();

        // Verifikasi rute Warga
        $this->actingAs($warga)->get(route('warga.laporan.saya'))->assertOk();
        $this->actingAs($petugas)->get(route('warga.laporan.saya'))->assertForbidden();
        $this->actingAs($admin)->get(route('warga.laporan.saya'))->assertForbidden();
    }
}
