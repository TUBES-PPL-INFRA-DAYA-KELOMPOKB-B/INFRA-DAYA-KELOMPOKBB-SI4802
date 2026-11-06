<?php

namespace Tests\Browser;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/**
 * PBI-4: Pengujian E2E User & Role Management (CRUD Pengguna).
 * Mengacu pada PRD Skenario T-01 s/d T-05 dan T-13.
 * PIC: Muhammad Daffa Fachrurozi (102022400151)
 */
class UserTest extends DuskTestCase
{
    use DatabaseMigrations;

    private function loginAsAdmin(Browser $browser): User
    {
        $admin = User::factory()->create([
            'email' => 'admin.test.'.uniqid().'@dsdabm.bandung.go.id',
            'role' => 'admin',
            'is_active' => true,
        ]);
        $browser->loginAs($admin);

        return $admin;
    }

    /**
     * T-01: Admin menambah user dengan data valid.
     * Hasil yang diharapkan: User tersimpan, muncul di daftar.
     * (Catatan PBI-4: Pemeriksaan log aktivitas akun ditandai menunggu PBI-6).
     */
    public function test_t01_admin_creates_user_with_valid_data(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsAdmin($browser);

            $uniqueEmail = 'petugas.'.uniqid().'@dsdabm.bandung.go.id';

            $browser->visit(route('admin.users.create'))
                ->type('name', 'Petugas Baru Lapangan')
                ->type('email', $uniqueEmail)
                ->type('phone', '081233445566')
                ->select('role', 'petugas')
                ->type('password', 'password123')
                ->type('password_confirmation', 'password123')
                ->click('@submit-btn')
                ->waitForLocation('/admin/users')
                ->assertSee('berhasil ditambahkan')
                ->assertSee('Petugas Baru Lapangan');
        });
    }

    /**
     * T-02: Tambah user dengan email duplikat.
     * Hasil yang diharapkan: Muncul error validasi, data tidak tersimpan.
     */
    public function test_t02_admin_cannot_create_user_with_duplicate_email(): void
    {
        $existingUser = User::factory()->create([
            'email' => 'duplikat.'.uniqid().'@dsdabm.bandung.go.id',
            'role' => 'petugas',
        ]);

        $this->browse(function (Browser $browser) use ($existingUser) {
            $this->loginAsAdmin($browser);

            $browser->visit(route('admin.users.create'))
                ->type('name', 'User Duplikat Test')
                ->type('email', $existingUser->email)
                ->type('phone', '081299998888')
                ->select('role', 'petugas')
                ->type('password', 'password123')
                ->type('password_confirmation', 'password123')
                ->click('@submit-btn')
                ->waitForText('Email tersebut sudah terdaftar')
                ->assertPathIs('/admin/users/create')
                ->assertSee('Email tersebut sudah terdaftar');
        });
    }

    /**
     * T-03: Admin mengubah data user.
     * Hasil yang diharapkan: Data berubah, pesan sukses tampil.
     * (Catatan PBI-4: Pemeriksaan log perubahan ditandai menunggu PBI-6).
     */
    public function test_t03_admin_updates_user_data(): void
    {
        $user = User::factory()->create([
            'name' => 'Nama Sebelum Edit',
            'email' => 'user.edit.'.uniqid().'@test.com',
            'role' => 'petugas',
        ]);

        $this->browse(function (Browser $browser) use ($user) {
            $this->loginAsAdmin($browser);

            $browser->visit(route('admin.users.edit', $user->id))
                ->clear('name')
                ->type('name', 'Nama Setelah Diperbarui')
                ->click('@submit-btn')
                ->waitForLocation('/admin/users')
                ->assertSee('berhasil diperbarui')
                ->assertSee('Nama Setelah Diperbarui');
        });
    }

    /**
     * T-04: Admin menghapus user (dengan konfirmasi modal).
     * Hasil yang diharapkan: User hilang dari daftar.
     * (Catatan PBI-4: Pemeriksaan log penghapusan ditandai menunggu PBI-6).
     */
    public function test_t04_admin_deletes_user_with_confirmation(): void
    {
        $targetUser = User::factory()->create([
            'name' => 'Pengguna Dihapus',
            'email' => 'hapus.'.uniqid().'@test.com',
            'role' => 'warga',
        ]);

        $this->browse(function (Browser $browser) use ($targetUser) {
            $this->loginAsAdmin($browser);

            $browser->visit(route('admin.users.index'))
                ->assertSee('Pengguna Dihapus')
                ->click("@delete-btn-{$targetUser->id}")
                ->waitFor('#deleteModal')
                ->click('@confirm-delete-btn')
                ->waitForLocation('/admin/users')
                ->assertSee('Pengguna berhasil dihapus')
                ->assertDontSee($targetUser->email);
        });
    }

    /**
     * T-05: Admin mencoba menghapus akunnya sendiri.
     * Hasil yang diharapkan: Tombol hapus akun sendiri tidak tersedia pada tampilan antarmuka.
     */
    public function test_t05_admin_cannot_delete_own_account_in_ui(): void
    {
        $this->browse(function (Browser $browser) {
            $admin = $this->loginAsAdmin($browser);

            $browser->visit(route('admin.users.index'))
                ->assertSee($admin->name)
                ->assertMissing("@delete-btn-{$admin->id}");
        });
    }

    /**
     * T-13: Filter dan pencarian di daftar user.
     * Hasil yang diharapkan: Daftar pengguna tersaring akurat berdasarkan kata kunci dan peran.
     * (Catatan PBI-4: Filter pencarian halaman log audit ditandai menunggu PBI-6).
     */
    public function test_t13_filter_and_search_in_user_list(): void
    {
        $uniqueKeyword = 'CariSaya'.rand(100, 999);
        $userKhusus = User::factory()->create([
            'name' => $uniqueKeyword.' Pengguna',
            'email' => strtolower($uniqueKeyword).'@test.com',
            'role' => 'petugas',
        ]);

        $userLain = User::factory()->create([
            'name' => 'Warga Normal',
            'email' => 'warga.normal.'.uniqid().'@test.com',
            'role' => 'warga',
        ]);

        $this->browse(function (Browser $browser) use ($userKhusus, $userLain, $uniqueKeyword) {
            $this->loginAsAdmin($browser);

            // Uji Pencarian Teks
            $browser->visit(route('admin.users.index'))
                ->type('@search-input', $uniqueKeyword)
                ->click('@search-btn')
                ->assertSee($userKhusus->name)
                ->assertDontSee($userLain->name);

            // Uji Filter Role
            $browser->visit(route('admin.users.index'))
                ->select('@role-select', 'warga')
                ->waitForLocation('/admin/users')
                ->assertSee($userLain->name)
                ->assertDontSee($userKhusus->name);
        });
    }

    /**
     * AC 4.3: Admin dapat melihat detail profil pengguna.
     */
    public function test_admin_can_view_user_detail(): void
    {
        $targetUser = User::factory()->create([
            'name' => 'Petugas Detail E2E',
            'email' => 'petugas.detail.'.uniqid().'@test.com',
            'role' => 'petugas',
        ]);

        $this->browse(function (Browser $browser) use ($targetUser) {
            $this->loginAsAdmin($browser);

            $browser->visit(route('admin.users.index'))
                ->click("@show-btn-{$targetUser->id}")
                ->waitForLocation("/admin/users/{$targetUser->id}")
                ->assertSee('Detail Pengguna')
                ->assertSee($targetUser->name)
                ->assertSee('Petugas Lapangan')
                ->assertSee('Menunggu PBI-6');
        });
    }

    /**
     * Non-admin (warga) dilarang mengakses manajemen pengguna (403).
     */
    public function test_non_admin_cannot_access_user_management(): void
    {
        $warga = User::factory()->create([
            'email' => 'warga.block.'.uniqid().'@test.com',
            'role' => 'warga',
        ]);

        $this->browse(function (Browser $browser) use ($warga) {
            $browser->loginAs($warga)
                ->visit(route('admin.users.index'))
                ->assertSee('403');
        });
    }
}
