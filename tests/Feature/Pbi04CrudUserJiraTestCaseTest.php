<?php

namespace Tests\Feature;

use App\Models\DisposisiPenugasan;
use App\Models\KategoriInfrastruktur;
use App\Models\Kelurahan;
use App\Models\LaporanKerusakan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Pengujian Resmi PBI-4 (CRUD User / IFD-8)
 * Menguji 12 Test Case Resmi (TC01-01 s/d TC01-12) dari Jira PPL Kelompok BB.
 */
class Pbi04CrudUserJiraTestCaseTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminA1;
    protected User $userU1;
    protected User $userU2;
    protected User $userU3;

    protected function setUp(): void
    {
        parent::setUp();

        // A1: Admin Aktif
        $this->adminA1 = User::factory()->create([
            'name' => 'Admin DSDABM',
            'email' => 'admin.a1@dsdabm.bandung.go.id',
            'role' => 'admin',
            'is_active' => true,
            'password' => Hash::make('AdminPassword123'),
        ]);

        // U1: Akun Belum Memiliki Transaksi (Budi)
        $this->userU1 = User::factory()->create([
            'name' => 'Budi Santoso',
            'email' => 'budi@bandung.go.id',
            'phone' => '08122334455',
            'role' => 'petugas',
            'is_active' => true,
            'password' => Hash::make('BudiPassword123'),
        ]);

        // U2: Akun Warga (Siti)
        $this->userU2 = User::factory()->create([
            'name' => 'Siti Aminah',
            'email' => 'siti@gmail.com',
            'phone' => '087788990011',
            'role' => 'warga',
            'is_active' => true,
            'password' => Hash::make('SitiPassword123'),
        ]);

        // U3: Akun Petugas Lapangan
        $this->userU3 = User::factory()->create([
            'name' => 'Ujang Teknisi',
            'email' => 'ujang@dsdabm.bandung.go.id',
            'phone' => '085566778899',
            'role' => 'petugas',
            'is_active' => true,
            'password' => Hash::make('UjangPassword123'),
        ]);
    }

    /**
     * TC01-01 (Normal):
     * Prasyarat: A1 login; U1, U2, U3 tersedia
     * Langkah: Buka menu Kelola Pengguna
     * Input: Sesi A1
     * Expected Output: Daftar akun U1, U2, dan U3 tampil beserta status aktifnya
     */
    public function test_tc01_01_buka_menu_kelola_pengguna_daftar_tampil_dengan_status_aktif(): void
    {
        $response = $this->actingAs($this->adminA1)->get(route('admin.users.index'));

        $response->assertOk();
        $response->assertSee('Kelola Pengguna');

        // U1 tampil
        $response->assertSee($this->userU1->name);
        $response->assertSee($this->userU1->email);

        // U2 tampil
        $response->assertSee($this->userU2->name);
        $response->assertSee($this->userU2->email);

        // U3 tampil
        $response->assertSee($this->userU3->name);
        $response->assertSee($this->userU3->email);

        // Status aktif tampil
        $response->assertSee('Aktif');
    }

    /**
     * TC01-02 (Normal):
     * Prasyarat: A1 login
     * Langkah: Cari pengguna berdasarkan nama dan email secara terpisah
     * Input: Siti; siti@gmail.com
     * Expected Output: Hanya akun U2 yang cocok ditampilkan
     */
    public function test_tc01_02_cari_pengguna_nama_dan_email_secara_terpisah(): void
    {
        // 1. Pencarian berdasarkan Nama: "Siti"
        $responseNama = $this->actingAs($this->adminA1)
            ->get(route('admin.users.index', ['search' => 'Siti']));

        $responseNama->assertOk();
        $responseNama->assertSee('Siti Aminah');
        $responseNama->assertSee('siti@gmail.com');
        $responseNama->assertDontSee('Budi Santoso');
        $responseNama->assertDontSee('Ujang Teknisi');

        // 2. Pencarian berdasarkan Email: "siti@gmail.com"
        $responseEmail = $this->actingAs($this->adminA1)
            ->get(route('admin.users.index', ['search' => 'siti@gmail.com']));

        $responseEmail->assertOk();
        $responseEmail->assertSee('Siti Aminah');
        $responseEmail->assertSee('siti@gmail.com');
        $responseEmail->assertDontSee('Budi Santoso');
        $responseEmail->assertDontSee('Ujang Teknisi');
    }

    /**
     * TC01-03 (Exception):
     * Prasyarat: A1 login
     * Langkah: Cari kata kunci tanpa kecocokan
     * Input: TidakAdaUserXYZ
     * Expected Output: Pesan data tidak ditemukan tampil; tidak terjadi error
     */
    public function test_tc01_03_cari_kata_kunci_tanpa_kecocokan(): void
    {
        $response = $this->actingAs($this->adminA1)
            ->get(route('admin.users.index', ['search' => 'TidakAdaUserXYZ']));

        $response->assertOk();
        $response->assertSee('Tidak ada data pengguna ditemukan.');
        $response->assertDontSee('Budi Santoso');
        $response->assertDontSee('Siti Aminah');
        $response->assertDontSee('Ujang Teknisi');
    }

    /**
     * TC01-04 (Normal):
     * Prasyarat: A1 login
     * Langkah: Klik Tambah Pengguna → isi seluruh data valid → Simpan
     * Input: Nama: Rudi, Email: rudi@bandung.go.id, Telp: 08123456789
     * Expected Output: Akun baru tersimpan di database dengan status aktif dan tampil pada daftar
     */
    public function test_tc01_04_tambah_pengguna_seluruh_data_valid_tersimpan_dan_aktif(): void
    {
        $payload = [
            'name' => 'Rudi',
            'email' => 'rudi@bandung.go.id',
            'phone' => '08123456789',
            'role' => 'petugas',
            'password' => 'RudiPassword123',
            'password_confirmation' => 'RudiPassword123',
        ];

        $response = $this->actingAs($this->adminA1)
            ->post(route('admin.users.store'), $payload);

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('success');

        // Pastikan akun tersimpan di database dan berstatus aktif
        $this->assertDatabaseHas('users', [
            'name' => 'Rudi',
            'email' => 'rudi@bandung.go.id',
            'phone' => '08123456789',
            'role' => 'petugas',
            'is_active' => true,
        ]);

        // Verifikasi tampil di daftar
        $listResponse = $this->actingAs($this->adminA1)->get(route('admin.users.index'));
        $listResponse->assertSee('Rudi');
        $listResponse->assertSee('rudi@bandung.go.id');
    }

    /**
     * TC01-05 (Exception):
     * Prasyarat: A1 login; U1 tersedia
     * Langkah: Tambah pengguna dengan email yang sudah terdaftar
     * Input: Email: budi@bandung.go.id
     * Expected Output: Ditolak oleh sistem; pesan validasi email duplikat tampil
     */
    public function test_tc01_05_tambah_pengguna_email_duplikat_ditolak(): void
    {
        $payload = [
            'name' => 'Budi Duplikat',
            'email' => 'budi@bandung.go.id', // email milik U1
            'phone' => '089988776655',
            'role' => 'petugas',
            'password' => 'PasswordBaru123',
            'password_confirmation' => 'PasswordBaru123',
        ];

        $response = $this->actingAs($this->adminA1)
            ->from(route('admin.users.create'))
            ->post(route('admin.users.store'), $payload);

        $response->assertRedirect(route('admin.users.create'));
        $response->assertSessionHasErrors(['email']);
    }

    /**
     * TC01-06 (Normal):
     * Prasyarat: A1 login; U1 tersedia
     * Langkah: Pilih Ubah pada U1 → perbarui nama & nomor telepon → Simpan
     * Input: Nama: Budi S. Teknik, Telp: 081299990000
     * Expected Output: Perubahan tersimpan di UI dan database
     */
    public function test_tc01_06_ubah_pengguna_nama_dan_nomor_telepon(): void
    {
        $payload = [
            'name' => 'Budi S. Teknik',
            'email' => $this->userU1->email,
            'phone' => '081299990000',
            'role' => $this->userU1->role,
        ];

        $response = $this->actingAs($this->adminA1)
            ->put(route('admin.users.update', $this->userU1->id), $payload);

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('success');

        // Pastikan perubahan masuk ke database
        $this->userU1->refresh();
        $this->assertEquals('Budi S. Teknik', $this->userU1->name);
        $this->assertEquals('081299990000', $this->userU1->phone);

        // Verifikasi di UI index
        $listResponse = $this->actingAs($this->adminA1)->get(route('admin.users.index'));
        $listResponse->assertSee('Budi S. Teknik');
        $listResponse->assertSee('081299990000');
    }

    /**
     * TC01-07 (Normal):
     * Prasyarat: A1 login; U1 belum memiliki transaksi
     * Langkah: Pilih Hapus pada U1 → konfirmasi penghapusan
     * Input: Target: U1
     * Expected Output: Akun U1 berhasil dihapus dari database dan hilang dari daftar pengguna
     */
    public function test_tc01_07_hapus_pengguna_tanpa_transaksi_berhasil(): void
    {
        $u1Id = $this->userU1->id;

        $response = $this->actingAs($this->adminA1)
            ->delete(route('admin.users.destroy', $u1Id));

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('success', 'Pengguna berhasil dihapus.');

        // Hilang dari database
        $this->assertDatabaseMissing('users', ['id' => $u1Id]);

        // Hilang dari tampilan antarmuka
        $listResponse = $this->actingAs($this->adminA1)->get(route('admin.users.index'));
        $listResponse->assertDontSee('budi@bandung.go.id');
    }

    /**
     * TC01-08 (Exception):
     * Prasyarat: A1 login; U2 sudah memiliki transaksi laporan
     * Langkah: Pilih Hapus pada U2 (melalui UI maupun request DELETE langsung)
     * Input: Target: U2
     * Expected Output: Penghapusan ditolak; sistem menampilkan pesan bahwa U2 memiliki riwayat transaksi dan hanya dapat dinonaktifkan
     */
    public function test_tc01_08_hapus_pengguna_dengan_transaksi_laporan_ditolak_hanya_dapat_dinonaktifkan(): void
    {
        $kelurahan = Kelurahan::factory()->create();
        $kategori = KategoriInfrastruktur::factory()->create();

        // Buat transaksi laporan milik U2
        $laporan = LaporanKerusakan::create([
            'nomor_tiket' => 'LP-001',
            'warga_id' => $this->userU2->id,
            'kelurahan_id' => $kelurahan->id,
            'kategori_id' => $kategori->id,
            'judul' => 'Saluran Air Tersumbat Jalan Merdeka',
            'deskripsi_kondisi' => 'Air meluap ke badan jalan',
            'latitude' => -6.917464,
            'longitude' => 107.619123,
            'foto_bukti_sebelum' => 'laporan/lp-001.jpg',
            'status' => 'menunggu_verifikasi',
        ]);

        $response = $this->actingAs($this->adminA1)
            ->delete(route('admin.users.destroy', $this->userU2->id));

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('error');

        // Pesan error menyatakan memiliki riwayat transaksi dan hanya dapat dinonaktifkan
        $errorMessage = session('error');
        $this->assertStringContainsString('riwayat transaksi', $errorMessage);
        $this->assertStringContainsString('dinonaktifkan', $errorMessage);

        // Record U2 dan laporan LP-001 tetap utuh di database
        $this->assertDatabaseHas('users', ['id' => $this->userU2->id]);
        $this->assertDatabaseHas('laporan_kerusakan', ['nomor_tiket' => 'LP-001']);
    }

    /**
     * TC01-09 (Exception):
     * Prasyarat: A1 login; U3 sudah memiliki transaksi disposisi
     * Langkah: Pilih Hapus pada U3
     * Input: Target: U3
     * Expected Output: Penghapusan ditolak; record U3 dan disposisi DP-001 tetap utuh
     */
    public function test_tc01_09_hapus_pengguna_dengan_transaksi_disposisi_ditolak_record_tetap_utuh(): void
    {
        $kelurahan = Kelurahan::factory()->create();
        $kategori = KategoriInfrastruktur::factory()->create();

        $laporan = LaporanKerusakan::create([
            'nomor_tiket' => 'LP-002',
            'warga_id' => $this->userU2->id,
            'kelurahan_id' => $kelurahan->id,
            'kategori_id' => $kategori->id,
            'judul' => 'Jalan Berlubang di Asia Afrika',
            'deskripsi_kondisi' => 'Lubang sedalam 15 cm',
            'latitude' => -6.921852,
            'longitude' => 107.607412,
            'foto_bukti_sebelum' => 'laporan/lp-002.jpg',
            'status' => 'diproses',
        ]);

        $disposisi = DisposisiPenugasan::create([
            'nomor_surat_tugas' => 'DP-001',
            'laporan_id' => $laporan->id,
            'admin_id' => $this->adminA1->id,
            'petugas_id' => $this->userU3->id,
            'tanggal_disposisi' => now(),
            'tenggat_waktu' => now()->addDays(3),
            'catatan_instruksi' => 'Lakukan penambalan aspal segera.',
        ]);

        // Coba hapus U3
        $response = $this->actingAs($this->adminA1)
            ->delete(route('admin.users.destroy', $this->userU3->id));

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('error');

        // Record U3 dan disposisi DP-001 tetap utuh
        $this->assertDatabaseHas('users', ['id' => $this->userU3->id]);
        $this->assertDatabaseHas('disposisi_penugasan', ['nomor_surat_tugas' => 'DP-001']);
    }

    /**
     * TC01-10 (Normal):
     * Prasyarat: A1 login; U2 memiliki transaksi dan gagal dihapus
     * Langkah: Pilih Nonaktifkan pada U2 → konfirmasi → periksa laporan LP-001
     * Input: Target: U2 (is_active = 0)
     * Expected Output: Status U2 berubah menjadi Nonaktif; record U2 dan laporan LP-001 tetap tersimpan utuh
     */
    public function test_tc01_10_nonaktifkan_pengguna_dengan_transaksi_laporan_tetap_tersimpan_utuh(): void
    {
        $kelurahan = Kelurahan::factory()->create();
        $kategori = KategoriInfrastruktur::factory()->create();

        $laporan = LaporanKerusakan::create([
            'nomor_tiket' => 'LP-001',
            'warga_id' => $this->userU2->id,
            'kelurahan_id' => $kelurahan->id,
            'kategori_id' => $kategori->id,
            'judul' => 'Penerangan Jalan Mati',
            'deskripsi_kondisi' => 'Lampu PJU mati sepanjang 100m',
            'latitude' => -6.917464,
            'longitude' => 107.619123,
            'foto_bukti_sebelum' => 'laporan/lp-001.jpg',
            'status' => 'menunggu_verifikasi',
        ]);

        // Nonaktifkan U2 via toggleActive endpoint
        $response = $this->actingAs($this->adminA1)
            ->patch(route('admin.users.toggle-active', $this->userU2->id));

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('success');

        // Status U2 berubah menjadi nonaktif
        $this->userU2->refresh();
        $this->assertFalse((bool) $this->userU2->is_active);

        // Record U2 dan laporan LP-001 tetap tersimpan utuh
        $this->assertDatabaseHas('users', [
            'id' => $this->userU2->id,
            'is_active' => false,
        ]);
        $this->assertDatabaseHas('laporan_kerusakan', [
            'nomor_tiket' => 'LP-001',
            'warga_id' => $this->userU2->id,
        ]);
    }

    /**
     * TC01-11 (Exception):
     * Prasyarat: U2 telah dinonaktifkan
     * Langkah: Login menggunakan kredensial U2 yang benar
     * Input: Email & password U2
     * Expected Output: Login ditolak; sistem menampilkan pesan bahwa akun telah dinonaktifkan
     */
    public function test_tc01_11_login_pengguna_dinonaktifkan_ditolak(): void
    {
        // Set U2 nonaktif
        $this->userU2->update(['is_active' => false]);

        $response = $this->post(route('login'), [
            'email' => 'siti@gmail.com',
            'password' => 'SitiPassword123',
        ]);

        $response->assertSessionHasErrors(['email']);
        $this->assertGuest();

        $errorMsg = session('errors')->first('email');
        $this->assertStringContainsString('dinonaktifkan', $errorMsg);
    }

    /**
     * TC01-12 (Exception):
     * Prasyarat: U2 (Warga) aktif login
     * Langkah: Akses URL Kelola Pengguna dan kirim request mutasi langsung
     * Input: Request oleh U2
     * Expected Output: Akses ditolak (403 Forbidden); data tetap aman
     */
    public function test_tc01_12_warga_aktif_akses_kelola_pengguna_dan_request_mutasi_ditolak_403(): void
    {
        // 1. Akses halaman index kelola pengguna
        $indexResponse = $this->actingAs($this->userU2)->get(route('admin.users.index'));
        $indexResponse->assertForbidden();

        // 2. Akses halaman form tambah pengguna
        $createResponse = $this->actingAs($this->userU2)->get(route('admin.users.create'));
        $createResponse->assertForbidden();

        // 3. Kirim request POST simpan pengguna baru
        $storeResponse = $this->actingAs($this->userU2)->post(route('admin.users.store'), [
            'name' => 'Akun Ilegal',
            'email' => 'ilegal@bandung.go.id',
            'role' => 'admin',
            'password' => 'PasswordIlegal123',
            'password_confirmation' => 'PasswordIlegal123',
        ]);
        $storeResponse->assertForbidden();

        // 4. Kirim request DELETE hapus pengguna lain
        $deleteResponse = $this->actingAs($this->userU2)->delete(route('admin.users.destroy', $this->userU1->id));
        $deleteResponse->assertForbidden();

        // 5. Kirim request PATCH ubah status aktif
        $toggleResponse = $this->actingAs($this->userU2)->patch(route('admin.users.toggle-active', $this->userU1->id));
        $toggleResponse->assertForbidden();

        // Verifikasi data tetap aman
        $this->assertDatabaseMissing('users', ['email' => 'ilegal@bandung.go.id']);
        $this->assertDatabaseHas('users', ['id' => $this->userU1->id]);
    }
}
