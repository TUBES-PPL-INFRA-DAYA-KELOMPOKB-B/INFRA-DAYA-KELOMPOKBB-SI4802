<?php

namespace Tests\Feature;

use App\Models\KategoriInfrastruktur;
use App\Models\Kecamatan;
use App\Models\Kelurahan;
use App\Models\LaporanKerusakan;
use App\Models\User;
use App\Services\LaporanService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * PBI-11 (Epic IFD-5): Pengujian Otomatis Tingkat Keparahan Default Kategori Kerusakan.
 *
 * Menguji seluruh Kriteria Penerimaan dan Skenario Pengujian T-11.1 sampai T-11.4:
 * - T-11.1: Admin menyimpan kategori tanpa tingkat keparahan -> Ditolak validasi
 * - T-11.2: Admin memilih tingkat keparahan (Rendah/Sedang/Tinggi/Kritis) -> bobot_prioritas otomatis & badge sesuai
 * - T-11.3: Warga membuat laporan pada kategori berdefault "Kritis" -> prioritas awal laporan "kritis"
 * - T-11.4: Perubahan default kategori tidak mengubah laporan yang sudah ada, hanya berlaku untuk laporan baru
 */
class KategoriPbi11TingkatKeparahanTest extends TestCase
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

    /**
     * T-11.1 & AC 4: Formulir kategori dikirim tanpa tingkat keparahan, ditolak dengan pesan validasi.
     */
    public function test_t11_1_admin_menyimpan_kategori_tanpa_tingkat_keparahan_ditolak(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->post(route('admin.kategori.store'), [
            'nama_kategori' => 'Kategori Tanpa Keparahan',
            'deskripsi' => 'Deskripsi uji coba',
            'tingkat_keparahan_default' => '',
        ]);

        $response->assertSessionHasErrors(['tingkat_keparahan_default']);
        $this->assertDatabaseMissing('kategori_infrastruktur', [
            'nama_kategori' => 'Kategori Tanpa Keparahan',
        ]);
    }

    /**
     * Validasi: Tingkat keparahan hanya menerima salah satu dari rendah, sedang, tinggi, kritis.
     */
    public function test_validasi_tingkat_keparahan_invalid_ditolak(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->post(route('admin.kategori.store'), [
            'nama_kategori' => 'Kategori Invalid',
            'tingkat_keparahan_default' => 'ekstrem_parah',
        ]);

        $response->assertSessionHasErrors(['tingkat_keparahan_default']);
    }

    /**
     * T-11.2 & AC 1: Admin menambah kategori PJU dengan tingkat keparahan "Sedang",
     * bobot_prioritas otomatis bernilai 2 dan badge "Sedang" tampil di daftar.
     */
    public function test_t11_2_admin_menambah_kategori_sedang_bobot_2_dan_badge_sesuai(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->post(route('admin.kategori.store'), [
            'nama_kategori' => 'Penerangan Jalan Umum (PJU)',
            'deskripsi' => 'Lampu jalan mati atau kabel putus',
            'tingkat_keparahan_default' => 'sedang',
        ]);

        $response->assertRedirect(route('admin.kategori.index'));

        // Cek database: bobot_prioritas harus otomatis terisi 2
        $this->assertDatabaseHas('kategori_infrastruktur', [
            'nama_kategori' => 'Penerangan Jalan Umum (PJU)',
            'tingkat_keparahan_default' => 'sedang',
            'bobot_prioritas' => 2,
        ]);

        // Cek tampilan daftar kategori memuat badge "Sedang"
        $indexResponse = $this->actingAs($admin)->get(route('admin.kategori.index'));
        $indexResponse->assertOk();
        $indexResponse->assertSee('Sedang (Bobot 2)');
    }

    /**
     * T-11.2: Admin memilih tingkat keparahan "Tinggi", bobot_prioritas bernilai 3 dan badge sesuai.
     */
    public function test_t11_2_admin_menambah_kategori_tinggi_bobot_3_dan_badge_sesuai(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->post(route('admin.kategori.store'), [
            'nama_kategori' => 'Saluran Drainase Rusak Parah',
            'tingkat_keparahan_default' => 'tinggi',
        ]);

        $response->assertRedirect(route('admin.kategori.index'));

        $this->assertDatabaseHas('kategori_infrastruktur', [
            'nama_kategori' => 'Saluran Drainase Rusak Parah',
            'tingkat_keparahan_default' => 'tinggi',
            'bobot_prioritas' => 3,
        ]);

        $indexResponse = $this->actingAs($admin)->get(route('admin.kategori.index'));
        $indexResponse->assertSee('Tinggi (Bobot 3)');
    }

    /**
     * T-11.2: Admin memilih tingkat keparahan "Kritis", bobot_prioritas bernilai 4 dan badge sesuai.
     */
    public function test_t11_2_admin_menambah_kategori_kritis_bobot_4_dan_badge_sesuai(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->post(route('admin.kategori.store'), [
            'nama_kategori' => 'Jembatan Ambruk / Tanggul Jebol',
            'tingkat_keparahan_default' => 'kritis',
        ]);

        $response->assertRedirect(route('admin.kategori.index'));

        $this->assertDatabaseHas('kategori_infrastruktur', [
            'nama_kategori' => 'Jembatan Ambruk / Tanggul Jebol',
            'tingkat_keparahan_default' => 'kritis',
            'bobot_prioritas' => 4,
        ]);

        $indexResponse = $this->actingAs($admin)->get(route('admin.kategori.index'));
        $indexResponse->assertSee('Kritis (Bobot 4)');
    }

    /**
     * T-11.2: Admin memilih tingkat keparahan "Rendah", bobot_prioritas bernilai 1 dan badge sesuai.
     */
    public function test_t11_2_admin_menambah_kategori_rendah_bobot_1_dan_badge_sesuai(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->post(route('admin.kategori.store'), [
            'nama_kategori' => 'Pengecatan Marka Pudar',
            'tingkat_keparahan_default' => 'rendah',
        ]);

        $response->assertRedirect(route('admin.kategori.index'));

        $this->assertDatabaseHas('kategori_infrastruktur', [
            'nama_kategori' => 'Pengecatan Marka Pudar',
            'tingkat_keparahan_default' => 'rendah',
            'bobot_prioritas' => 1,
        ]);

        $indexResponse = $this->actingAs($admin)->get(route('admin.kategori.index'));
        $indexResponse->assertSee('Rendah (Bobot 1)');
    }

    /**
     * T-11.3 & AC 2: Kategori memiliki default "Kritis", saat warga membuat laporan
     * pada kategori tersebut, prioritas awal laporan secara otomatis bernilai "kritis".
     */
    public function test_t11_3_laporan_baru_menggunakan_tingkat_keparahan_default_kategori_kritis(): void
    {
        Storage::fake('public');

        $warga = $this->createWarga();
        $kecamatan = Kecamatan::factory()->create();
        $kelurahan = Kelurahan::factory()->create(['kecamatan_id' => $kecamatan->id]);

        $kategoriKritis = KategoriInfrastruktur::factory()->create([
            'nama_kategori' => 'Jembatan / Tanggul Air',
            'tingkat_keparahan_default' => 'kritis',
            'bobot_prioritas' => 4,
        ]);

        $foto = UploadedFile::fake()->image('jembatan_rusak.jpg');

        $laporanService = app(LaporanService::class);
        $laporan = $laporanService->createLaporan($warga->id, [
            'kelurahan_id' => $kelurahan->id,
            'kategori_id' => $kategoriKritis->id,
            'judul' => 'Tanggul Sungai Jebol Mengancam Pemukiman',
            'deskripsi_kondisi' => 'Tanggul jebol selebar 5 meter air mulai meluap',
            'latitude' => -6.914744,
            'longitude' => 107.609810,
        ], $foto);

        $this->assertEquals('kritis', $laporan->prioritas);
        $this->assertDatabaseHas('laporan_kerusakan', [
            'id' => $laporan->id,
            'kategori_id' => $kategoriKritis->id,
            'prioritas' => 'kritis',
        ]);
    }

    /**
     * T-11.4 & AC 3: Admin mengubah default kategori dari "Sedang" ke "Tinggi",
     * laporan lama tidak berubah dan laporan baru memakai nilai "Tinggi".
     */
    public function test_t11_4_perubahan_default_kategori_tidak_mengubah_laporan_lama(): void
    {
        Storage::fake('public');

        $admin = $this->createAdmin();
        $warga = $this->createWarga();
        $kecamatan = Kecamatan::factory()->create();
        $kelurahan = Kelurahan::factory()->create(['kecamatan_id' => $kecamatan->id]);

        // Kategori awal dengan default "sedang"
        $kategori = KategoriInfrastruktur::factory()->create([
            'nama_kategori' => 'PJU Padam',
            'tingkat_keparahan_default' => 'sedang',
            'bobot_prioritas' => 2,
        ]);

        $foto1 = UploadedFile::fake()->image('pju1.jpg');
        $laporanService = app(LaporanService::class);

        // Laporan lama dibuat saat default masih "sedang"
        $laporanLama = $laporanService->createLaporan($warga->id, [
            'kelurahan_id' => $kelurahan->id,
            'kategori_id' => $kategori->id,
            'judul' => 'Laporan Lama PJU',
            'deskripsi_kondisi' => 'Lampu jalan mati',
            'latitude' => -6.914744,
            'longitude' => 107.609810,
        ], $foto1);

        $this->assertEquals('sedang', $laporanLama->prioritas);

        // Admin mengubah tingkat keparahan default kategori dari "sedang" ke "tinggi"
        $response = $this->actingAs($admin)->put(route('admin.kategori.update', $kategori->id), [
            'nama_kategori' => 'PJU Padam',
            'tingkat_keparahan_default' => 'tinggi',
        ]);
        $response->assertRedirect(route('admin.kategori.index'));

        // Pastikan kategori terupdate di database dengan bobot 3
        $kategori->refresh();
        $this->assertEquals('tinggi', $kategori->tingkat_keparahan_default);
        $this->assertEquals(3, $kategori->bobot_prioritas);

        // Verifikasi: Laporan lama TIDAK berubah (tetap "sedang")
        $laporanLama->refresh();
        $this->assertEquals('sedang', $laporanLama->prioritas);

        // Laporan baru dibuat setelah perubahan, harus menggunakan nilai "tinggi"
        $foto2 = UploadedFile::fake()->image('pju2.jpg');
        $laporanBaru = $laporanService->createLaporan($warga->id, [
            'kelurahan_id' => $kelurahan->id,
            'kategori_id' => $kategori->id,
            'judul' => 'Laporan Baru PJU Setelah Update',
            'deskripsi_kondisi' => 'Lampu jalan padam dan rawan kejahatan',
            'latitude' => -6.914744,
            'longitude' => 107.609810,
        ], $foto2);

        $this->assertEquals('tinggi', $laporanBaru->prioritas);
        $this->assertDatabaseHas('laporan_kerusakan', [
            'id' => $laporanBaru->id,
            'prioritas' => 'tinggi',
        ]);
    }

    /**
     * KF-11.2: Halaman formulir create & edit kategori menyajikan opsi dropdown tingkat keparahan.
     */
    public function test_kf11_2_formulir_kategori_menyajikan_opsi_dropdown_tingkat_keparahan(): void
    {
        $admin = $this->createAdmin();

        $kategori = KategoriInfrastruktur::factory()->create([
            'nama_kategori' => 'Kategori Edit Test',
            'tingkat_keparahan_default' => 'kritis',
            'bobot_prioritas' => 4,
        ]);

        // Cek halaman create
        $createResponse = $this->actingAs($admin)->get(route('admin.kategori.create'));
        $createResponse->assertOk();
        $createResponse->assertSee('Tingkat Keparahan Default');
        $createResponse->assertSee('name="tingkat_keparahan_default"', false);
        $createResponse->assertSee('value="kritis"', false);
        $createResponse->assertSee('value="tinggi"', false);
        $createResponse->assertSee('value="sedang"', false);
        $createResponse->assertSee('value="rendah"', false);

        // Cek halaman edit
        $editResponse = $this->actingAs($admin)->get(route('admin.kategori.edit', $kategori->id));
        $editResponse->assertOk();
        $editResponse->assertSee('Tingkat Keparahan Default');
        $editResponse->assertSee('name="tingkat_keparahan_default"', false);
        $editResponse->assertSee('value="kritis"', false);
    }
}

