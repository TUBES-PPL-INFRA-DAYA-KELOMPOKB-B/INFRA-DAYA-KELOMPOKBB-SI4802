<?php

namespace Tests\Feature;

use App\Models\KategoriInfrastruktur;
use App\Models\Kelurahan;
use App\Models\LaporanKerusakan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Pengujian Fitur PBI-4: CRUD User & Role Management.
 * Sesuai Acceptance Criteria PRD AC 4.1 - 4.6.
 */
class UserManagementCrudTest extends TestCase
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
     * AC 4.1: Daftar User dengan pagination, pencarian nama/email, dan filter role.
     */
    public function test_admin_can_view_paginated_users_with_search_and_role_filter(): void
    {
        $admin = $this->createAdmin();

        $user1 = User::factory()->create(['name' => 'Asep Surya', 'email' => 'asep.surya@example.com', 'role' => 'petugas']);
        $user2 = User::factory()->create(['name' => 'Bambang Sudrajat', 'email' => 'bambang@example.com', 'role' => 'warga']);
        $user3 = User::factory()->create(['name' => 'Cecep Ridwan', 'email' => 'cecep@dsdabm.bandung.go.id', 'role' => 'admin']);

        // 1. Akses index biasa
        $response = $this->actingAs($admin)->get(route('admin.users.index'));
        $response->assertOk();
        $response->assertSee('Kelola Pengguna');
        $response->assertSee('Asep Surya');
        $response->assertSee('Bambang Sudrajat');

        // 2. Pencarian berdasarkan nama
        $searchResponse = $this->actingAs($admin)->get(route('admin.users.index', ['search' => 'Asep']));
        $searchResponse->assertOk();
        $searchResponse->assertSee('Asep Surya');
        $searchResponse->assertDontSee('Bambang Sudrajat');

        // 3. Pencarian berdasarkan email
        $emailSearchResponse = $this->actingAs($admin)->get(route('admin.users.index', ['search' => 'bambang@example.com']));
        $emailSearchResponse->assertOk();
        $emailSearchResponse->assertSee('Bambang Sudrajat');
        $emailSearchResponse->assertDontSee('Asep Surya');

        // 4. Filter berdasarkan peran (role)
        $filterResponse = $this->actingAs($admin)->get(route('admin.users.index', ['role' => 'petugas']));
        $filterResponse->assertOk();
        $filterResponse->assertSee('Asep Surya');
        $filterResponse->assertDontSee('Bambang Sudrajat');
    }

    /**
     * AC 4.2: Tambah user dengan data valid, email unik, password min 8 tersimpan Bcrypt, pesan berhasil.
     */
    public function test_admin_can_create_user_with_valid_data(): void
    {
        $admin = $this->createAdmin();

        $userData = [
            'name' => 'Daffa Fachrurozi',
            'email' => 'daffa.fachrurozi@dsdabm.bandung.go.id',
            'phone' => '081298765432',
            'role' => 'petugas',
            'password' => 'PasswordRahasia123',
            'password_confirmation' => 'PasswordRahasia123',
        ];

        $response = $this->actingAs($admin)->post(route('admin.users.store'), $userData);

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('success', 'Pengguna baru berhasil ditambahkan.');

        $this->assertDatabaseHas('users', [
            'name' => 'Daffa Fachrurozi',
            'email' => 'daffa.fachrurozi@dsdabm.bandung.go.id',
            'phone' => '081298765432',
            'role' => 'petugas',
        ]);

        $createdUser = User::where('email', 'daffa.fachrurozi@dsdabm.bandung.go.id')->first();
        $this->assertNotNull($createdUser);
        $this->assertTrue(Hash::check('PasswordRahasia123', $createdUser->password));
    }

    /**
     * AC 4.2 & 4.6: Validasi pembuatan pengguna gagal jika email duplikat atau password < 8 karakter.
     */
    public function test_user_creation_validation_fails_for_duplicate_email_or_short_password(): void
    {
        $admin = $this->createAdmin();
        $existing = User::factory()->create(['email' => 'duplikat@bandung.go.id']);

        // Test email duplikat & password kurang dari 8 karakter
        $response = $this->actingAs($admin)->post(route('admin.users.store'), [
            'name' => 'User Duplikat',
            'email' => 'duplikat@bandung.go.id',
            'role' => 'warga',
            'password' => '12345',
            'password_confirmation' => '12345',
        ]);

        $response->assertSessionHasErrors(['email', 'password']);
    }

    /**
     * AC 4.3: Admin dapat melihat detail user (data akun, role, status, dan riwayat).
     */
    public function test_admin_can_view_user_detail(): void
    {
        $admin = $this->createAdmin();
        $targetUser = User::factory()->create([
            'name' => 'Petugas Lapangan Teladan',
            'email' => 'petugas.teladan@dsdabm.bandung.go.id',
            'role' => 'petugas',
            'phone' => '081122334455',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.users.show', $targetUser->id));

        $response->assertOk();
        $response->assertSee('Detail Pengguna');
        $response->assertSee('Petugas Lapangan Teladan');
        $response->assertSee('petugas.teladan@dsdabm.bandung.go.id');
        $response->assertSee('Petugas Lapangan');
        $response->assertSee('Menunggu PBI-6');
    }

    /**
     * AC 4.4: Admin dapat mengubah user, password opsional saat edit (tidak berubah jika kosong).
     */
    public function test_admin_can_update_user_without_changing_password(): void
    {
        $admin = $this->createAdmin();
        $targetUser = User::factory()->create([
            'name' => 'Nama Awal',
            'email' => 'email.awal@bandung.go.id',
            'role' => 'warga',
            'password' => Hash::make('PasswordAwal123'),
        ]);

        $updateData = [
            'name' => 'Nama Diperbarui',
            'email' => 'email.awal@bandung.go.id',
            'phone' => '089988776655',
            'role' => 'petugas',
            'password' => '',
            'password_confirmation' => '',
        ];

        $response = $this->actingAs($admin)->put(route('admin.users.update', $targetUser->id), $updateData);

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('success', 'Data pengguna berhasil diperbarui.');

        $targetUser->refresh();
        $this->assertEquals('Nama Diperbarui', $targetUser->name);
        $this->assertEquals('petugas', $targetUser->role);
        $this->assertEquals('089988776655', $targetUser->phone);
        // Password lama tetap valid
        $this->assertTrue(Hash::check('PasswordAwal123', $targetUser->password));
    }

    /**
     * AC 4.4: Mengubah user dengan password baru minimal 8 karakter.
     */
    public function test_admin_can_update_user_password(): void
    {
        $admin = $this->createAdmin();
        $targetUser = User::factory()->create([
            'password' => Hash::make('PasswordLama123'),
        ]);

        $updateData = [
            'name' => $targetUser->name,
            'email' => $targetUser->email,
            'role' => $targetUser->role,
            'password' => 'PasswordBaruBaru456',
            'password_confirmation' => 'PasswordBaruBaru456',
        ];

        $response = $this->actingAs($admin)->put(route('admin.users.update', $targetUser->id), $updateData);

        $response->assertRedirect(route('admin.users.index'));

        $targetUser->refresh();
        $this->assertTrue(Hash::check('PasswordBaruBaru456', $targetUser->password));
    }

    /**
     * AC 4.4: Validasi email unik saat edit mengecualikan pengguna itu sendiri.
     */
    public function test_update_user_ignores_self_unique_email_but_rejects_other_users_email(): void
    {
        $admin = $this->createAdmin();
        $user1 = User::factory()->create(['email' => 'user1@bandung.go.id']);
        $user2 = User::factory()->create(['email' => 'user2@bandung.go.id']);

        // Menggunakan email sendiri tetap valid
        $selfEmailResponse = $this->actingAs($admin)->put(route('admin.users.update', $user1->id), [
            'name' => 'User Satu Updated',
            'email' => 'user1@bandung.go.id',
            'role' => $user1->role,
        ]);
        $selfEmailResponse->assertSessionDoesntHaveErrors(['email']);

        // Menggunakan email user lain ditolak
        $otherEmailResponse = $this->actingAs($admin)->put(route('admin.users.update', $user1->id), [
            'name' => 'User Satu Duplikat',
            'email' => 'user2@bandung.go.id',
            'role' => $user1->role,
        ]);
        $otherEmailResponse->assertSessionHasErrors(['email']);
    }

    /**
     * AC 4.5: Admin tidak dapat menghapus akunnya sendiri.
     */
    public function test_admin_cannot_delete_self_account(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->delete(route('admin.users.destroy', $admin->id));

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('error', 'Anda tidak dapat menghapus akun Anda sendiri saat sedang masuk.');

        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }

    /**
     * AC 4.5: User yang memiliki relasi data (laporan atau penugasan) dilindungi dari penghapusan.
     */
    public function test_user_with_related_data_cannot_be_deleted(): void
    {
        $admin = $this->createAdmin();

        $warga = User::factory()->create(['role' => 'warga']);
        $kelurahan = Kelurahan::factory()->create();
        $kategori = KategoriInfrastruktur::factory()->create();

        // Buat laporan terkait
        LaporanKerusakan::create([
            'nomor_tiket' => 'INFRA-TEST-0001',
            'warga_id' => $warga->id,
            'kelurahan_id' => $kelurahan->id,
            'kategori_id' => $kategori->id,
            'judul' => 'Jalan Berlubang Dekat Simpang',
            'deskripsi_kondisi' => 'Kondisi lubang cukup dalam',
            'latitude' => -6.914744,
            'longitude' => 107.609810,
            'foto_bukti_sebelum' => 'laporan/dummy.jpg',
            'status' => 'menunggu_verifikasi',
        ]);

        $this->assertTrue($warga->hasRelatedData());

        $response = $this->actingAs($admin)->delete(route('admin.users.destroy', $warga->id));

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('error');

        $this->assertDatabaseHas('users', ['id' => $warga->id]);
    }

    /**
     * AC 4.5: User tanpa relasi data dapat dihapus dengan sukses.
     */
    public function test_user_without_related_data_can_be_deleted(): void
    {
        $admin = $this->createAdmin();
        $targetUser = User::factory()->create(['role' => 'warga']);

        $response = $this->actingAs($admin)->delete(route('admin.users.destroy', $targetUser->id));

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('success', 'Pengguna berhasil dihapus.');

        $this->assertDatabaseMissing('users', ['id' => $targetUser->id]);
    }

    /**
     * Batasan: Modul ini hanya boleh diakses oleh Admin.
     */
    public function test_non_admin_roles_and_guests_cannot_access_user_crud(): void
    {
        $warga = User::factory()->create(['role' => 'warga']);
        $petugas = User::factory()->create(['role' => 'petugas']);
        $pimpinan = User::factory()->create(['role' => 'pimpinan']);

        // Guest redirect ke login
        $this->get(route('admin.users.index'))->assertRedirect(route('login'));

        // Warga 403
        $this->actingAs($warga)->get(route('admin.users.index'))->assertForbidden();

        // Petugas 403
        $this->actingAs($petugas)->get(route('admin.users.index'))->assertForbidden();

        // Pimpinan 403
        $this->actingAs($pimpinan)->get(route('admin.users.index'))->assertForbidden();
    }
}
