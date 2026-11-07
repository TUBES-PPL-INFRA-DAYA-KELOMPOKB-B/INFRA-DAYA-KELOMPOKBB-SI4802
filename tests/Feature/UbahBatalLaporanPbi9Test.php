<?php

namespace Tests\Feature;

use App\Models\KategoriInfrastruktur;
use App\Models\Kecamatan;
use App\Models\Kelurahan;
use App\Models\LaporanKerusakan;
use App\Models\RiwayatStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UbahBatalLaporanPbi9Test extends TestCase
{
    use RefreshDatabase;

    private User $wargaA;

    private User $wargaB;

    private Kecamatan $kecamatan;

    private Kelurahan $kelurahan;

    private KategoriInfrastruktur $kategori;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');

        $this->wargaA = User::factory()->create(['role' => 'warga', 'is_active' => true]);
        $this->wargaB = User::factory()->create(['role' => 'warga', 'is_active' => true]);

        $this->kecamatan = Kecamatan::create([
            'kode_kecamatan' => 'BDG-01',
            'nama_kecamatan' => 'Coblong',
        ]);

        $this->kelurahan = Kelurahan::create([
            'kecamatan_id' => $this->kecamatan->id,
            'kode_kelurahan' => 'BDG-01-01',
            'nama_kelurahan' => 'Dago',
        ]);

        $this->kategori = KategoriInfrastruktur::create([
            'nama_kategori' => 'Jalan Raya',
            'deskripsi' => 'Kerusakan jalan',
            'bobot_prioritas' => 3,
        ]);
    }

    private function createSampleLaporan(string $status = 'menunggu_verifikasi'): LaporanKerusakan
    {
        return LaporanKerusakan::create([
            'nomor_tiket' => 'INFRA-20261107-0001',
            'warga_id' => $this->wargaA->id,
            'kelurahan_id' => $this->kelurahan->id,
            'kategori_id' => $this->kategori->id,
            'judul' => 'Jalan Rusak di Dago Bawah',
            'deskripsi_kondisi' => 'Aspal mengelupas cukup dalam sekitar 50cm.',
            'latitude' => -6.91746400,
            'longitude' => 107.61912300,
            'foto_bukti_sebelum' => 'laporan/sebelum/foto_lama.jpg',
            'status' => $status,
            'prioritas' => 'sedang',
        ]);
    }

    /**
     * Warga pemilik dapat mengubah data laporan berstatus 'menunggu_verifikasi'.
     */
    public function test_warga_pemilik_dapat_mengubah_data_laporan_menunggu_verifikasi(): void
    {
        $laporan = $this->createSampleLaporan('menunggu_verifikasi');

        $payload = [
            'judul' => 'Judul Laporan Diperbarui',
            'kategori_id' => $this->kategori->id,
            'kelurahan_id' => $this->kelurahan->id,
            'deskripsi_kondisi' => 'Deskripsi kondisi yang telah diperbaiki keterangannya oleh pelapor.',
            'patokan_lokasi' => 'Depan Kafe Dago Corner',
            'latitude' => -6.90500000,
            'longitude' => 107.61500000,
        ];

        $response = $this->actingAs($this->wargaA)
            ->put(route('warga.laporan.update', $laporan->id), $payload);

        $response->assertRedirect(route('warga.laporan.show', $laporan->id));
        $response->assertSessionHas('success');

        $laporan->refresh();
        $this->assertEquals('Judul Laporan Diperbarui', $laporan->judul);
        $this->assertStringContainsString('Depan Kafe Dago Corner', $laporan->deskripsi_kondisi);
        $this->assertEquals(-6.90500000, $laporan->latitude);
        $this->assertEquals(107.61500000, $laporan->longitude);

        $this->assertDatabaseHas('riwayat_status', [
            'laporan_id' => $laporan->id,
            'user_id' => $this->wargaA->id,
            'status' => 'menunggu_verifikasi',
        ]);
    }

    /**
     * Warga pemilik dapat mengganti foto bukti dengan berkas valid (<= 5 MB, jpg/jpeg/png).
     */
    public function test_warga_pemilik_dapat_mengganti_foto_bukti_dengan_berkas_valid(): void
    {
        $laporan = $this->createSampleLaporan('menunggu_verifikasi');
        $newPhoto = UploadedFile::fake()->image('foto_baru.png', 800, 600)->size(2048); // 2 MB

        $payload = [
            'judul' => $laporan->judul,
            'kategori_id' => $this->kategori->id,
            'kelurahan_id' => $this->kelurahan->id,
            'deskripsi_kondisi' => $laporan->deskripsi_kondisi,
            'latitude' => $laporan->latitude,
            'longitude' => $laporan->longitude,
            'foto_bukti_sebelum' => $newPhoto,
        ];

        $response = $this->actingAs($this->wargaA)
            ->put(route('warga.laporan.update', $laporan->id), $payload);

        $response->assertRedirect(route('warga.laporan.show', $laporan->id));

        $laporan->refresh();
        $this->assertNotEquals('laporan/sebelum/foto_lama.jpg', $laporan->foto_bukti_sebelum);
        Storage::disk('public')->assertExists($laporan->foto_bukti_sebelum);
    }

    /**
     * Penggantian foto bukti melebihi 5 MB ditolak validasi (NFR-02).
     */
    public function test_upload_penggantian_foto_melebihi_5mb_ditolak(): void
    {
        $laporan = $this->createSampleLaporan('menunggu_verifikasi');
        $oversizedPhoto = UploadedFile::fake()->create('foto_berat.jpg', 6000, 'image/jpeg'); // ~6 MB

        $payload = [
            'judul' => $laporan->judul,
            'kategori_id' => $this->kategori->id,
            'kelurahan_id' => $this->kelurahan->id,
            'deskripsi_kondisi' => $laporan->deskripsi_kondisi,
            'latitude' => $laporan->latitude,
            'longitude' => $laporan->longitude,
            'foto_bukti_sebelum' => $oversizedPhoto,
        ];

        $response = $this->actingAs($this->wargaA)
            ->put(route('warga.laporan.update', $laporan->id), $payload);

        $response->assertSessionHasErrors('foto_bukti_sebelum');
    }

    /**
     * Penggantian foto bukti dengan format berkas tidak valid ditolak.
     */
    public function test_upload_penggantian_foto_format_tidak_valid_ditolak(): void
    {
        $laporan = $this->createSampleLaporan('menunggu_verifikasi');
        $invalidFile = UploadedFile::fake()->create('dokumen.pdf', 500, 'application/pdf');

        $payload = [
            'judul' => $laporan->judul,
            'kategori_id' => $this->kategori->id,
            'kelurahan_id' => $this->kelurahan->id,
            'deskripsi_kondisi' => $laporan->deskripsi_kondisi,
            'latitude' => $laporan->latitude,
            'longitude' => $laporan->longitude,
            'foto_bukti_sebelum' => $invalidFile,
        ];

        $response = $this->actingAs($this->wargaA)
            ->put(route('warga.laporan.update', $laporan->id), $payload);

        $response->assertSessionHasErrors('foto_bukti_sebelum');
    }

    /**
     * Proteksi IDOR: Warga lain dilarang mengubah laporan milik orang lain.
     */
    public function test_warga_lain_dilarang_mengubah_laporan_orang_lain_idor(): void
    {
        $laporan = $this->createSampleLaporan('menunggu_verifikasi');

        $payload = [
            'judul' => 'Judul Dibajak Warga Lain',
            'kategori_id' => $this->kategori->id,
            'kelurahan_id' => $this->kelurahan->id,
            'deskripsi_kondisi' => 'Mencoba memodifikasi data laporan warga lain.',
            'latitude' => -6.91746400,
            'longitude' => 107.61912300,
        ];

        // Warga B mencoba mengirim PUT ke laporan Warga A
        $response = $this->actingAs($this->wargaB)
            ->put(route('warga.laporan.update', $laporan->id), $payload);

        $response->assertForbidden();

        $laporan->refresh();
        $this->assertEquals('Jalan Rusak di Dago Bawah', $laporan->judul);
    }

    /**
     * Proteksi IDOR: Warga lain dilarang membatalkan laporan milik orang lain.
     */
    public function test_warga_lain_dilarang_membatalkan_laporan_orang_lain_idor(): void
    {
        $laporan = $this->createSampleLaporan('menunggu_verifikasi');

        // Warga B mencoba membatalkan laporan Warga A
        $response = $this->actingAs($this->wargaB)
            ->delete(route('warga.laporan.destroy', $laporan->id), [
                'alasan_pembatalan' => 'Mencoba membatalkan tanpa izin.',
            ]);

        $response->assertForbidden();

        $laporan->refresh();
        $this->assertEquals('menunggu_verifikasi', $laporan->status);
    }

    /**
     * Penolakan edit: Laporan berstatus bukan 'menunggu_verifikasi' ditolak untuk diubah.
     */
    public function test_laporan_dengan_status_bukan_menunggu_verifikasi_ditolak_untuk_diubah(): void
    {
        $laporan = $this->createSampleLaporan('diproses');

        $payload = [
            'judul' => 'Mencoba Mengubah Saat Diproses',
            'kategori_id' => $this->kategori->id,
            'kelurahan_id' => $this->kelurahan->id,
            'deskripsi_kondisi' => 'Deskripsi baru saat status sudah diproses.',
            'latitude' => -6.91746400,
            'longitude' => 107.61912300,
        ];

        $response = $this->actingAs($this->wargaA)
            ->put(route('warga.laporan.update', $laporan->id), $payload);

        $response->assertForbidden();

        $laporan->refresh();
        $this->assertEquals('Jalan Rusak di Dago Bawah', $laporan->judul);
    }

    /**
     * Penolakan batal: Laporan berstatus bukan 'menunggu_verifikasi' ditolak untuk dibatalkan.
     */
    public function test_laporan_dengan_status_bukan_menunggu_verifikasi_ditolak_untuk_dibatalkan(): void
    {
        $laporan = $this->createSampleLaporan('diproses');

        $response = $this->actingAs($this->wargaA)
            ->delete(route('warga.laporan.destroy', $laporan->id), [
                'alasan_pembatalan' => 'Ingin membatalkan saat laporan sudah dikerjakan.',
            ]);

        $response->assertForbidden();

        $laporan->refresh();
        $this->assertEquals('diproses', $laporan->status);
    }

    /**
     * Warga dapat membatalkan laporan dengan alasan valid, status berubah menjadi 'dibatalkan',
     * dan riwayat status tercatat.
     */
    public function test_warga_dapat_membatalkan_laporan_dan_status_berubah_menjadi_dibatalkan(): void
    {
        $laporan = $this->createSampleLaporan('menunggu_verifikasi');

        $response = $this->actingAs($this->wargaA)
            ->delete(route('warga.laporan.destroy', $laporan->id), [
                'alasan_pembatalan' => 'Kerusakan aspal telah diperbaiki secara mandiri oleh warga setempat.',
            ]);

        $response->assertRedirect(route('warga.laporan.saya'));
        $response->assertSessionHas('success');

        $laporan->refresh();
        $this->assertEquals('dibatalkan', $laporan->status);
        $this->assertEquals('Kerusakan aspal telah diperbaiki secara mandiri oleh warga setempat.', $laporan->alasan_pembatalan);

        $this->assertDatabaseHas('riwayat_status', [
            'laporan_id' => $laporan->id,
            'user_id' => $this->wargaA->id,
            'status' => 'dibatalkan',
        ]);

        $riwayatTerbaru = RiwayatStatus::where('laporan_id', $laporan->id)
            ->where('status', 'dibatalkan')
            ->first();

        $this->assertNotNull($riwayatTerbaru);
        $this->assertStringContainsString('Kerusakan aspal telah diperbaiki secara mandiri', $riwayatTerbaru->keterangan);
    }

    /**
     * Pembatalan laporan wajib menyertakan alasan pembatalan minimal 5 karakter.
     */
    public function test_pembatalan_laporan_wajib_menyertakan_alasan_pembatalan(): void
    {
        $laporan = $this->createSampleLaporan('menunggu_verifikasi');

        // Mengirimkan pembatalan tanpa alasan
        $response = $this->actingAs($this->wargaA)
            ->delete(route('warga.laporan.destroy', $laporan->id), [
                'alasan_pembatalan' => '',
            ]);

        $response->assertSessionHasErrors('alasan_pembatalan');

        $laporan->refresh();
        $this->assertEquals('menunggu_verifikasi', $laporan->status);

        // Mengirimkan alasan terlalu pendek (< 5 karakter)
        $responseShort = $this->actingAs($this->wargaA)
            ->delete(route('warga.laporan.destroy', $laporan->id), [
                'alasan_pembatalan' => 'test',
            ]);

        $responseShort->assertSessionHasErrors('alasan_pembatalan');
        $laporan->refresh();
        $this->assertEquals('menunggu_verifikasi', $laporan->status);
    }

    /**
     * Halaman edit menampilkan mode readonly terkunci saat status sudah diproses.
     */
    public function test_halaman_edit_menampilkan_mode_readonly_terkunci_saat_status_diproses(): void
    {
        $laporan = $this->createSampleLaporan('diproses');

        $response = $this->actingAs($this->wargaA)
            ->get(route('warga.laporan.edit', $laporan->id));

        $response->assertOk();
        $response->assertSee('Formulir Terkunci (Read-Only)');
        $response->assertSee('Simpan Perubahan (Terkunci)');
        $response->assertSee('readonly', false);
    }
}
