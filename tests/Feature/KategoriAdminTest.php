<?php

namespace Tests\Feature;

use App\Models\KategoriInfrastruktur;
use App\Models\Kelurahan;
use App\Models\LaporanKerusakan;
use App\Models\User;
use Database\Seeders\KategoriInfrastrukturSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * PBI-10 (Epic IFD-5): Pengujian Otomatis CRUD Kategori Kerusakan (Admin DSDABM).
 *
 * Menguji seluruh Kriteria Penerimaan dan Skenario Pengujian T-10.1 sampai T-10.6:
 * - T-10.1: Admin menambah kategori valid
 * - T-10.2: Admin menambah kategori dengan nama duplikat (case-insensitive)
 * - T-10.3: Admin mengubah nama dan deskripsi kategori
 * - T-10.4: Admin menghapus kategori yang belum dipakai
 * - T-10.5: Admin menghapus kategori yang sudah dipakai laporan (ditolak + pesan)
 * - T-10.6: Non-admin (Warga, Petugas, Pimpinan) mengakses rute kelola kategori (HTTP 403)
 */
class KategoriAdminTest extends TestCase
{
    use RefreshDatabase;

    private function createAdmin(): User
    {
        return User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);
    }

    private function createWarga(): User
    {
        return User::factory()->create([
            'role' => 'warga',
            'is_active' => true,
        ]);
    }

    private function createPetugas(): User
    {
        return User::factory()->create([
            'role' => 'petugas',
            'is_active' => true,
        ]);
    }

    private function createPimpinan(): User
    {
        return User::factory()->create([
            'role' => 'pimpinan',
            'is_active' => true,
        ]);
    }

    /**
     * KF-10.1 & KF-10.6: Admin dapat melihat daftar kategori dengan paginasi dan jumlah laporan.
     */
    public function test_admin_dapat_melihat_daftar_kategori_dan_jumlah_laporan(): void
    {
        $admin = $this->createAdmin();

        $kategori = KategoriInfrastruktur::factory()->create([
            'nama_kategori' => 'Jalan Rusak Berlubang',
            'deskripsi' => 'Kerusakan jalan aspal',
            'bobot_prioritas' => 3,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.kategori.index'));

        $response->assertOk();
        $response->assertViewIs('admin.kategori.index');
        $response->assertSee('Kategori Infrastruktur');
        $response->assertSee('Jalan Rusak Berlubang');
        $response->assertSee('Jumlah Laporan');
        $response->assertSee('0 Laporan');
    }

    /**
     * T-10.1 & KF-10.2: Admin menambah kategori valid, data tersimpan dan tampil di daftar.
     */
    public function test_t10_1_admin_dapat_menambah_kategori_valid(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->post(route('admin.kategori.store'), [
            'nama_kategori' => 'Jembatan Penyeberangan Orang (JPO)',
            'bobot_prioritas' => 2,
            'deskripsi' => 'Kerusakan struktur atau lantai JPO umum',
        ]);

        $response->assertRedirect(route('admin.kategori.index'));
        $response->assertSessionHas('success', 'Kategori infrastruktur baru berhasil ditambahkan.');

        $this->assertDatabaseHas('kategori_infrastruktur', [
            'nama_kategori' => 'Jembatan Penyeberangan Orang (JPO)',
            'bobot_prioritas' => 2,
            'deskripsi' => 'Kerusakan struktur atau lantai JPO umum',
        ]);
    }

    /**
     * Validasi: nama kategori wajib diisi dan maksimal 100 karakter.
     */
    public function test_validasi_nama_kategori_wajib_dan_maksimal_100_karakter(): void
    {
        $admin = $this->createAdmin();

        // Nama kategori kosong
        $responseEmpty = $this->actingAs($admin)->post(route('admin.kategori.store'), [
            'nama_kategori' => '',
            'bobot_prioritas' => 2,
        ]);
        $responseEmpty->assertSessionHasErrors(['nama_kategori']);

        // Nama kategori melebihi 100 karakter
        $responseTooLong = $this->actingAs($admin)->post(route('admin.kategori.store'), [
            'nama_kategori' => str_repeat('A', 101),
            'bobot_prioritas' => 2,
        ]);
        $responseTooLong->assertSessionHasErrors(['nama_kategori']);
    }

    /**
     * T-10.2 & KF-10.5: Admin menambah kategori dengan nama duplikat ditolak (case-insensitive).
     * Pesan validasi: "Nama kategori sudah digunakan."
     */
    public function test_t10_2_admin_menambah_kategori_duplikat_ditolak_case_insensitive(): void
    {
        $admin = $this->createAdmin();

        KategoriInfrastruktur::factory()->create([
            'nama_kategori' => 'Saluran Drainase',
            'bobot_prioritas' => 3,
        ]);

        // Duplikat nama sama persis
        $responseExact = $this->actingAs($admin)->post(route('admin.kategori.store'), [
            'nama_kategori' => 'Saluran Drainase',
            'bobot_prioritas' => 2,
        ]);
        $responseExact->assertSessionHasErrors([
            'nama_kategori' => 'Nama kategori sudah digunakan.',
        ]);

        // Duplikat dengan huruf kecil semua (case-insensitive)
        $responseLower = $this->actingAs($admin)->post(route('admin.kategori.store'), [
            'nama_kategori' => 'saluran drainase',
            'bobot_prioritas' => 2,
        ]);
        $responseLower->assertSessionHasErrors([
            'nama_kategori' => 'Nama kategori sudah digunakan.',
        ]);

        // Duplikat dengan huruf besar semua
        $responseUpper = $this->actingAs($admin)->post(route('admin.kategori.store'), [
            'nama_kategori' => 'SALURAN DRAINASE',
            'bobot_prioritas' => 2,
        ]);
        $responseUpper->assertSessionHasErrors([
            'nama_kategori' => 'Nama kategori sudah digunakan.',
        ]);
    }

    /**
     * T-10.3 & KF-10.3: Admin mengubah nama dan deskripsi kategori.
     */
    public function test_t10_3_admin_dapat_mengubah_kategori(): void
    {
        $admin = $this->createAdmin();

        $kategori = KategoriInfrastruktur::factory()->create([
            'nama_kategori' => 'Kategori Lama',
            'deskripsi' => 'Deskripsi lama',
            'bobot_prioritas' => 1,
        ]);

        $response = $this->actingAs($admin)->put(route('admin.kategori.update', $kategori->id), [
            'nama_kategori' => 'Kategori Baru Diperbarui',
            'deskripsi' => 'Deskripsi telah diperbarui dengan benar',
            'bobot_prioritas' => 3,
        ]);

        $response->assertRedirect(route('admin.kategori.index'));
        $response->assertSessionHas('success', 'Data kategori infrastruktur berhasil diperbarui.');

        $this->assertDatabaseHas('kategori_infrastruktur', [
            'id' => $kategori->id,
            'nama_kategori' => 'Kategori Baru Diperbarui',
            'deskripsi' => 'Deskripsi telah diperbarui dengan benar',
            'bobot_prioritas' => 3,
        ]);
    }

    /**
     * T-10.4 & KF-10.4: Admin menghapus kategori yang belum dipakai laporan.
     */
    public function test_t10_4_admin_dapat_menghapus_kategori_yang_belum_dipakai(): void
    {
        $admin = $this->createAdmin();

        $kategori = KategoriInfrastruktur::factory()->create([
            'nama_kategori' => 'Kategori Tanpa Laporan',
        ]);

        $response = $this->actingAs($admin)->delete(route('admin.kategori.destroy', $kategori->id));

        $response->assertRedirect(route('admin.kategori.index'));
        $response->assertSessionHas('success', 'Kategori infrastruktur berhasil dihapus.');

        $this->assertDatabaseMissing('kategori_infrastruktur', [
            'id' => $kategori->id,
        ]);
    }

    /**
     * T-10.5 & KF-10.4: Admin menghapus kategori yang sudah dipakai laporan ditolak dengan pesan penjelasan.
     */
    public function test_t10_5_admin_menghapus_kategori_yang_sudah_dipakai_laporan_ditolak(): void
    {
        $admin = $this->createAdmin();
        $warga = $this->createWarga();
        $kelurahan = Kelurahan::factory()->create();

        $kategori = KategoriInfrastruktur::factory()->create([
            'nama_kategori' => 'Jalan Utama Kota',
        ]);

        // Buat minimal 1 laporan kerusakan yang berelasi dengan kategori ini
        LaporanKerusakan::factory()->create([
            'kategori_id' => $kategori->id,
            'warga_id' => $warga->id,
            'kelurahan_id' => $kelurahan->id,
        ]);

        $response = $this->actingAs($admin)->delete(route('admin.kategori.destroy', $kategori->id));

        $response->assertRedirect(route('admin.kategori.index'));
        $response->assertSessionHas('error', 'Kategori tidak dapat dihapus karena masih digunakan oleh laporan kerusakan.');

        // Pastikan kategori tetap ada di database
        $this->assertDatabaseHas('kategori_infrastruktur', [
            'id' => $kategori->id,
            'nama_kategori' => 'Jalan Utama Kota',
        ]);
    }

    /**
     * T-10.6: Pengguna non-admin (Warga, Petugas, Pimpinan) dilarang mengakses CRUD kategori (HTTP 403).
     */
    public function test_t10_6_non_admin_dilarang_mengakses_kelola_kategori(): void
    {
        $warga = $this->createWarga();
        $petugas = $this->createPetugas();
        $pimpinan = $this->createPimpinan();

        $kategori = KategoriInfrastruktur::factory()->create([
            'nama_kategori' => 'Kategori Uji Proteksi',
        ]);

        // Peran Warga ditolak (403)
        $this->actingAs($warga)->get(route('admin.kategori.index'))->assertForbidden();
        $this->actingAs($warga)->get(route('admin.kategori.create'))->assertForbidden();
        $this->actingAs($warga)->post(route('admin.kategori.store'), ['nama_kategori' => 'X', 'bobot_prioritas' => 1])->assertForbidden();
        $this->actingAs($warga)->get(route('admin.kategori.edit', $kategori->id))->assertForbidden();
        $this->actingAs($warga)->put(route('admin.kategori.update', $kategori->id), ['nama_kategori' => 'Y', 'bobot_prioritas' => 1])->assertForbidden();
        $this->actingAs($warga)->delete(route('admin.kategori.destroy', $kategori->id))->assertForbidden();

        // Peran Petugas ditolak (403)
        $this->actingAs($petugas)->get(route('admin.kategori.index'))->assertForbidden();
        $this->actingAs($petugas)->delete(route('admin.kategori.destroy', $kategori->id))->assertForbidden();

        // Peran Pimpinan ditolak (403)
        $this->actingAs($pimpinan)->get(route('admin.kategori.index'))->assertForbidden();
        $this->actingAs($pimpinan)->delete(route('admin.kategori.destroy', $kategori->id))->assertForbidden();
    }

    /**
     * Tamu / Guest diarahkan ke login saat mengakses rute kategori admin.
     */
    public function test_guest_diarahkan_ke_login_saat_akses_kategori_admin(): void
    {
        $response = $this->get(route('admin.kategori.index'));
        $response->assertRedirect(route('login'));
    }

    /**
     * KF-10.7: Perubahan nama kategori otomatis tercermin pada laporan terkait melalui kategori_id.
     */
    public function test_kf10_7_perubahan_nama_kategori_tercermin_pada_laporan_terkait(): void
    {
        $admin = $this->createAdmin();
        $warga = $this->createWarga();
        $kelurahan = Kelurahan::factory()->create();

        $kategori = KategoriInfrastruktur::factory()->create([
            'nama_kategori' => 'Gorong-gorong Rusak Awal',
        ]);

        $laporan = LaporanKerusakan::factory()->create([
            'kategori_id' => $kategori->id,
            'warga_id' => $warga->id,
            'kelurahan_id' => $kelurahan->id,
        ]);

        $this->assertEquals('Gorong-gorong Rusak Awal', $laporan->kategori->nama_kategori);

        // Update kategori
        $this->actingAs($admin)->put(route('admin.kategori.update', $kategori->id), [
            'nama_kategori' => 'Gorong-gorong Rusak Diperbarui',
            'bobot_prioritas' => 3,
        ]);

        $laporan->refresh();
        $this->assertEquals('Gorong-gorong Rusak Diperbarui', $laporan->kategori->nama_kategori);
    }

    /**
     * KF-10.8: Seeder menyediakan 4 kategori infrastruktur awal.
     */
    public function test_kf10_8_seeder_menyediakan_empat_kategori_awal(): void
    {
        $this->seed(KategoriInfrastrukturSeeder::class);

        $this->assertDatabaseHas('kategori_infrastruktur', [
            'nama_kategori' => 'Jalan Raya / Trotoar',
        ]);
        $this->assertDatabaseHas('kategori_infrastruktur', [
            'nama_kategori' => 'Saluran Drainase / Gorong-gorong',
        ]);
        $this->assertDatabaseHas('kategori_infrastruktur', [
            'nama_kategori' => 'Penerangan Jalan Umum (PJU)',
        ]);
        $this->assertDatabaseHas('kategori_infrastruktur', [
            'nama_kategori' => 'Jembatan / Tanggul Air',
        ]);

        $this->assertCount(4, KategoriInfrastruktur::all());
    }

    /**
     * KF-10.6: Paginasi berfungsi dengan 10 data per halaman.
     */
    public function test_kf10_6_daftar_kategori_dipaginasi_10_per_halaman(): void
    {
        $admin = $this->createAdmin();

        KategoriInfrastruktur::factory()->count(15)->create();

        $response = $this->actingAs($admin)->get(route('admin.kategori.index'));

        $response->assertOk();
        $this->assertCount(10, $response->viewData('kategoriList'));
    }
}

