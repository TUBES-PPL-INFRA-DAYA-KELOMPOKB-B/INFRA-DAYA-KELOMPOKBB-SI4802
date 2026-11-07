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
 * PBI-8: Pinpoint Lokasi Peta / Geotagging (Leaflet.js + OpenStreetMap).
 * Memvalidasi Request data koordinat (latitude & longitude desimal valid),
 * batas rentang geografis administratif Kota Bandung (-7.05 s/d -6.80 & 107.50 s/d 107.80),
 * input patokan lokasi, serta persistensi ke tabel laporan_kerusakan.
 */
class PinpointPetaPbi8Test extends TestCase
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
            'kode_kecamatan' => 'KEC-02',
            'nama_kecamatan' => 'Sumur Bandung',
        ]);

        $this->kelurahan = Kelurahan::create([
            'kecamatan_id' => $kecamatan->id,
            'kode_kelurahan' => 'KEL-02',
            'nama_kelurahan' => 'Braga',
        ]);

        $this->kategori = KategoriInfrastruktur::create([
            'nama_kategori' => 'Drainase',
            'deskripsi' => 'Saluran drainase perkotaan',
            'bobot_prioritas' => 2,
        ]);
    }

    /**
     * Koordinat presisi desimal valid tersimpan ke database (decimal 10,8 dan decimal 11,8).
     */
    public function test_koordinat_valid_tersimpan_presisi_ke_database(): void
    {
        $file = UploadedFile::fake()->image('kondisi_drainase.jpg')->size(1500);

        $payload = [
            'judul' => 'Drainase Tersumbat di Jalan Braga',
            'kategori_id' => $this->kategori->id,
            'kelurahan_id' => $this->kelurahan->id,
            'deskripsi_kondisi' => 'Air meluap hingga trotoar saat hujan deras.',
            'patokan_lokasi' => 'Depan Kafe Braga No. 10',
            'latitude' => -6.91746400,
            'longitude' => 107.61912300,
            'foto_bukti_sebelum' => $file,
        ];

        $response = $this->actingAs($this->warga)->post(route('warga.laporan.store'), $payload);

        $response->assertSessionHasNoErrors();
        $laporan = LaporanKerusakan::latest()->first();

        $this->assertNotNull($laporan);
        $this->assertEquals(-6.91746400, (float) $laporan->latitude);
        $this->assertEquals(107.61912300, (float) $laporan->longitude);

        // Verifikasi bahwa patokan lokasi terintegrasi dalam catatan kondisi
        $this->assertStringContainsString('Depan Kafe Braga No. 10', $laporan->deskripsi_kondisi);

        // Verifikasi pencatatan di database laporan_kerusakan
        $this->assertDatabaseHas('laporan_kerusakan', [
            'id' => $laporan->id,
            'warga_id' => $this->warga->id,
            'latitude' => -6.91746400,
            'longitude' => 107.61912300,
            'status' => 'menunggu_verifikasi',
        ]);
    }

    /**
     * Validasi: Koordinat latitude wajib diisi.
     */
    public function test_latitude_wajib_diisi(): void
    {
        $file = UploadedFile::fake()->image('bukti.jpg');

        $response = $this->actingAs($this->warga)->post(route('warga.laporan.store'), [
            'judul' => 'Jalan Rusak Tanpa Koordinat Lat',
            'kategori_id' => $this->kategori->id,
            'deskripsi_kondisi' => 'Deskripsi kerusakan',
            'longitude' => 107.61912300,
            'foto_bukti_sebelum' => $file,
        ]);

        $response->assertSessionHasErrors(['latitude']);
        $this->assertDatabaseCount('laporan_kerusakan', 0);
    }

    /**
     * Validasi: Koordinat longitude wajib diisi.
     */
    public function test_longitude_wajib_diisi(): void
    {
        $file = UploadedFile::fake()->image('bukti.jpg');

        $response = $this->actingAs($this->warga)->post(route('warga.laporan.store'), [
            'judul' => 'Jalan Rusak Tanpa Koordinat Lng',
            'kategori_id' => $this->kategori->id,
            'deskripsi_kondisi' => 'Deskripsi kerusakan',
            'latitude' => -6.91746400,
            'foto_bukti_sebelum' => $file,
        ]);

        $response->assertSessionHasErrors(['longitude']);
        $this->assertDatabaseCount('laporan_kerusakan', 0);
    }

    /**
     * Validasi: Format koordinat latitude harus berupa desimal numerik valid.
     */
    public function test_latitude_harus_berupa_angka_desimal_numerik(): void
    {
        $file = UploadedFile::fake()->image('bukti.jpg');

        $response = $this->actingAs($this->warga)->post(route('warga.laporan.store'), [
            'judul' => 'Koordinat Lat String Ilegal',
            'kategori_id' => $this->kategori->id,
            'deskripsi_kondisi' => 'Deskripsi kerusakan',
            'latitude' => 'bukan-desimal-angka',
            'longitude' => 107.61912300,
            'foto_bukti_sebelum' => $file,
        ]);

        $response->assertSessionHasErrors(['latitude']);
    }

    /**
     * Validasi: Format koordinat longitude harus berupa desimal numerik valid.
     */
    public function test_longitude_harus_berupa_angka_desimal_numerik(): void
    {
        $file = UploadedFile::fake()->image('bukti.jpg');

        $response = $this->actingAs($this->warga)->post(route('warga.laporan.store'), [
            'judul' => 'Koordinat Lng String Ilegal',
            'kategori_id' => $this->kategori->id,
            'deskripsi_kondisi' => 'Deskripsi kerusakan',
            'latitude' => -6.91746400,
            'longitude' => 'bukan-bujur-angka',
            'foto_bukti_sebelum' => $file,
        ]);

        $response->assertSessionHasErrors(['longitude']);
    }

    /**
     * Validasi: Titik latitude di luar batas administratif Kota Bandung (-7.05 s/d -6.80) ditolak.
     */
    public function test_latitude_di_luar_rentang_wilayah_bandung_ditolak(): void
    {
        $file = UploadedFile::fake()->image('bukti.jpg');

        // Koordinat DKI Jakarta (-6.20000000) yang berada di luar batas Kota Bandung
        $response = $this->actingAs($this->warga)->post(route('warga.laporan.store'), [
            'judul' => 'Titik Luar Bandung (Jakarta)',
            'kategori_id' => $this->kategori->id,
            'deskripsi_kondisi' => 'Deskripsi kerusakan',
            'latitude' => -6.20000000,
            'longitude' => 107.61912300,
            'foto_bukti_sebelum' => $file,
        ]);

        $response->assertSessionHasErrors(['latitude']);
        $this->assertDatabaseCount('laporan_kerusakan', 0);
    }

    /**
     * Validasi: Titik longitude di luar batas administratif Kota Bandung (107.50 s/d 107.80) ditolak.
     */
    public function test_longitude_di_luar_rentang_wilayah_bandung_ditolak(): void
    {
        $file = UploadedFile::fake()->image('bukti.jpg');

        // Koordinat Cirebon (108.55000000) yang berada di luar batas Kota Bandung
        $response = $this->actingAs($this->warga)->post(route('warga.laporan.store'), [
            'judul' => 'Titik Luar Bandung (Cirebon)',
            'kategori_id' => $this->kategori->id,
            'deskripsi_kondisi' => 'Deskripsi kerusakan',
            'latitude' => -6.91746400,
            'longitude' => 108.55000000,
            'foto_bukti_sebelum' => $file,
        ]);

        $response->assertSessionHasErrors(['longitude']);
        $this->assertDatabaseCount('laporan_kerusakan', 0);
    }

    /**
     * Titik koordinat tepat pada batas wilayah Kota Bandung diterima dan tersimpan presisi.
     */
    public function test_koordinat_batas_wilayah_bandung_diterima(): void
    {
        $file = UploadedFile::fake()->image('bukti_batas.jpg');

        $response = $this->actingAs($this->warga)->post(route('warga.laporan.store'), [
            'judul' => 'Laporan di Perbatasan Bandung',
            'kategori_id' => $this->kategori->id,
            'kelurahan_id' => $this->kelurahan->id,
            'deskripsi_kondisi' => 'Kondisi trotoar batas wilayah.',
            'latitude' => -7.05000000,
            'longitude' => 107.80000000,
            'foto_bukti_sebelum' => $file,
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('laporan_kerusakan', [
            'judul' => 'Laporan di Perbatasan Bandung',
            'latitude' => -7.05000000,
            'longitude' => 107.80000000,
        ]);
    }
}
