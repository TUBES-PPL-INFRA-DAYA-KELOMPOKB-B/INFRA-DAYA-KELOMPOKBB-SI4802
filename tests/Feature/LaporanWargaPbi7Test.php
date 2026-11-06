<?php

namespace Tests\Feature;

use App\Models\KategoriInfrastruktur;
use App\Models\Kecamatan;
use App\Models\Kelurahan;
use App\Models\LaporanKerusakan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * PBI-7: Form Isian Pelaporan Kerusakan Infrastruktur & Unggah Foto Bukti.
 * Memvalidasi Controller, Request Validation (Format berkas, Batas ukuran 5 MB),
 * Sanitasi Form (XSS Prevention), dan Pembuatan Tiket Aduan Baru Status Default "Menunggu Verifikasi".
 */
class LaporanWargaPbi7Test extends TestCase
{
    use RefreshDatabase;

    private User $warga;

    private KategoriInfrastruktur $kategori;

    private Kelurahan $kelurahan;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        $this->warga = User::factory()->create([
            'role' => 'warga',
            'is_active' => true,
        ]);

        $kecamatan = Kecamatan::create([
            'kode_kecamatan' => 'KEC-01',
            'nama_kecamatan' => 'Coblong',
        ]);

        $this->kelurahan = Kelurahan::create([
            'kecamatan_id' => $kecamatan->id,
            'kode_kelurahan' => 'KEL-01',
            'nama_kelurahan' => 'Dago',
        ]);

        $this->kategori = KategoriInfrastruktur::create([
            'nama_kategori' => 'Jalan Raya',
            'deskripsi' => 'Kerusakan aspal dan jalan',
            'bobot_prioritas' => 3,
        ]);
    }

    /**
     * Warga terotentikasi dapat melihat formulir pelaporan kerusakan (PBI-7 UI).
     */
    public function test_warga_dapat_mengakses_form_pelaporan(): void
    {
        $response = $this->actingAs($this->warga)->get(route('warga.laporan.create'));

        $response->assertOk();
        $response->assertSee('Lapor Kerusakan Infrastruktur');
        $response->assertSee('Kategori Infrastruktur');
        $response->assertSee('Foto Bukti Kerusakan');
        $response->assertSee($this->kategori->nama_kategori);
    }

    /**
     * Tamu tanpa autentikasi diarahkan ke halaman login.
     */
    public function test_guest_diredirect_ke_login_saat_akses_form_pelaporan(): void
    {
        $response = $this->get(route('warga.laporan.create'));

        $response->assertRedirect(route('login'));
    }

    /**
     * Non-warga (misalnya Admin atau Petugas) dilarang mengakses form pengajuan warga.
     */
    public function test_non_warga_dilarang_mengakses_form_pelaporan(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get(route('warga.laporan.create'));

        $response->assertForbidden();
    }

    /**
     * Warga berhasil membuat laporan dengan foto bukti fisik valid (.jpg).
     * Status default laporan harus 'menunggu_verifikasi'.
     */
    public function test_warga_dapat_mengirim_laporan_baru_dengan_foto_valid(): void
    {
        $file = UploadedFile::fake()->image('bukti_jalan.jpg', 600, 400)->size(2048); // 2 MB

        $response = $this->actingAs($this->warga)->post(route('warga.laporan.store'), [
            'judul' => 'Jalan Berlubang di Dekat Simpang Dago',
            'kategori_id' => $this->kategori->id,
            'kelurahan_id' => $this->kelurahan->id,
            'deskripsi_kondisi' => 'Terdapat lubang sedalam 15 cm di jalur utama yang membahayakan pengendara roda dua.',
            'latitude' => -6.90000000,
            'longitude' => 107.61000000,
            'foto_bukti_sebelum' => $file,
        ]);

        $laporan = LaporanKerusakan::latest()->first();

        $this->assertNotNull($laporan);
        $response->assertRedirect(route('warga.laporan.show', $laporan->id));
        $response->assertSessionHas('success');

        // Pastikan tiket berformat INFRA-YYYYMMDD-XXXX
        $this->assertMatchesRegularExpression('/^INFRA-\d{8}-\d{4}$/', $laporan->nomor_tiket);

        // Verifikasi ERD dan status default "Menunggu Verifikasi"
        $this->assertEquals('menunggu_verifikasi', $laporan->status);
        $this->assertEquals($this->warga->id, $laporan->warga_id);
        $this->assertEquals($this->kategori->id, $laporan->kategori_id);
        $this->assertEquals('Jalan Berlubang di Dekat Simpang Dago', $laporan->judul);

        // Verifikasi berkas tersimpan
        Storage::disk('public')->assertExists($laporan->foto_bukti_sebelum);

        // Verifikasi audit log di riwayat_status
        $this->assertDatabaseHas('riwayat_status', [
            'laporan_id' => $laporan->id,
            'user_id' => $this->warga->id,
            'status' => 'menunggu_verifikasi',
        ]);
    }

    /**
     * Format berkas foto valid .png dan .jpeg juga diterima.
     */
    public function test_format_berkas_png_dan_jpeg_diterima(): void
    {
        $pngFile = UploadedFile::fake()->image('bukti.png', 400, 300)->size(1024);

        $response = $this->actingAs($this->warga)->post(route('warga.laporan.store'), [
            'judul' => 'Saluran Air Meluap di Bahu Jalan',
            'kategori_id' => $this->kategori->id,
            'deskripsi_kondisi' => 'Saluran air tersumbat endapan lumpur.',
            'foto_bukti_sebelum' => $pngFile,
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('laporan_kerusakan', [
            'judul' => 'Saluran Air Meluap di Bahu Jalan',
            'status' => 'menunggu_verifikasi',
        ]);
    }

    /**
     * Validasi: Berkas foto bukti berukuran > 5 MB (5120 KB) harus ditolak sesuai NFR-02.
     */
    public function test_unggahan_foto_melebihi_5mb_ditolak_validasi(): void
    {
        $oversizedFile = UploadedFile::fake()->image('foto_besar.jpg')->size(5121); // 5.001 MB (> 5120 KB)

        $response = $this->actingAs($this->warga)->post(route('warga.laporan.store'), [
            'judul' => 'Jalan Rusak Berat',
            'kategori_id' => $this->kategori->id,
            'deskripsi_kondisi' => 'Kondisi aspal ambles.',
            'foto_bukti_sebelum' => $oversizedFile,
        ]);

        $response->assertSessionHasErrors(['foto_bukti_sebelum']);
        $this->assertDatabaseCount('laporan_kerusakan', 0);
    }

    /**
     * Validasi: Berkas dengan format ekstensi ilegal (.pdf, .txt, .exe) harus ditolak sesuai NFR-02.
     */
    public function test_unggahan_foto_format_tidak_valid_ditolak(): void
    {
        $invalidFile = UploadedFile::fake()->create('dokumen.pdf', 500, 'application/pdf');

        $response = $this->actingAs($this->warga)->post(route('warga.laporan.store'), [
            'judul' => 'Laporan Berkas PDF',
            'kategori_id' => $this->kategori->id,
            'deskripsi_kondisi' => 'Mencoba unggah berkas bukan gambar.',
            'foto_bukti_sebelum' => $invalidFile,
        ]);

        $response->assertSessionHasErrors(['foto_bukti_sebelum']);
        $this->assertDatabaseCount('laporan_kerusakan', 0);
    }

    /**
     * Validasi: Seluruh field wajib (judul, kategori_id, deskripsi_kondisi, foto_bukti_sebelum) harus diisi.
     */
    public function test_field_wajib_laporan_harus_diisi(): void
    {
        $response = $this->actingAs($this->warga)->post(route('warga.laporan.store'), []);

        $response->assertSessionHasErrors([
            'judul',
            'kategori_id',
            'deskripsi_kondisi',
            'foto_bukti_sebelum',
        ]);
        $this->assertDatabaseCount('laporan_kerusakan', 0);
    }

    /**
     * Sanitasi form: Input teks berbahaya (HTML/Script) pada judul dan deskripsi dibersihkan secara otomatis.
     */
    public function test_sanitasi_form_membersihkan_karakter_html_dan_script(): void
    {
        $file = UploadedFile::fake()->image('bukti.jpg', 200, 200)->size(500);

        $payload = [
            'judul' => '<script>alert("XSS")</script>Trotoar Rusak',
            'kategori_id' => $this->kategori->id,
            'deskripsi_kondisi' => '<b>Pecah</b> dan berantakan <iframe src="evil.com"></iframe>',
            'foto_bukti_sebelum' => $file,
        ];

        $response = $this->actingAs($this->warga)->post(route('warga.laporan.store'), $payload);

        $response->assertSessionHasNoErrors();

        $laporan = LaporanKerusakan::latest()->first();

        // Pastikan script dan iframe terhapus bersih
        $this->assertStringNotContainsString('<script>', $laporan->judul);
        $this->assertStringNotContainsString('alert', $laporan->judul);
        $this->assertStringNotContainsString('<iframe', $laporan->deskripsi_kondisi);
        $this->assertEquals('Trotoar Rusak', $laporan->judul);
        $this->assertEquals('Pecah dan berantakan', $laporan->deskripsi_kondisi);
    }
}
