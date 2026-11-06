<?php

namespace Tests\Feature;

use App\Models\KategoriInfrastruktur;
use App\Models\Kecamatan;
use App\Models\Kelurahan;
use App\Models\LaporanKerusakan;
use App\Models\UmpanBalik;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UmpanBalikTest extends TestCase
{
    use RefreshDatabase;

    private User $wargaA;

    private User $wargaB;

    private User $admin;

    private User $petugas;

    private LaporanKerusakan $laporanSelesai;

    private LaporanKerusakan $laporanDiproses;

    protected function setUp(): void
    {
        parent::setUp();

        $this->wargaA = User::factory()->create(['role' => 'warga', 'is_active' => true]);
        $this->wargaB = User::factory()->create(['role' => 'warga', 'is_active' => true]);
        $this->admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $this->petugas = User::factory()->create(['role' => 'petugas', 'is_active' => true]);

        $kecamatan = Kecamatan::create(['kode_kecamatan' => 'BDG-01', 'nama_kecamatan' => 'Coblong']);
        $kelurahan = Kelurahan::create([
            'kecamatan_id' => $kecamatan->id,
            'kode_kelurahan' => 'BDG-01-01',
            'nama_kelurahan' => 'Dago',
        ]);
        $kategori = KategoriInfrastruktur::create([
            'nama_kategori' => 'Jalan Rusak',
            'deskripsi' => 'Kerusakan aspal jalan',
            'bobot_prioritas' => 3,
        ]);

        $this->laporanSelesai = LaporanKerusakan::create([
            'nomor_tiket' => 'INFRA-20261014-9001',
            'warga_id' => $this->wargaA->id,
            'kategori_id' => $kategori->id,
            'kelurahan_id' => $kelurahan->id,
            'judul' => 'Jalan Rusak Depan Kampus',
            'deskripsi_kondisi' => 'Aspal retak buaya.',
            'latitude' => -6.8900000,
            'longitude' => 107.6100000,
            'status' => 'selesai',
            'prioritas' => 'tinggi',
            'foto_bukti_sebelum' => 'laporan/sebelum.jpg',
            'foto_bukti_sesudah' => 'laporan/sesudah.jpg',
            'catatan_perbaikan' => 'Sudah ditambal hotmix.',
            'selesai_at' => now(),
        ]);

        $this->laporanDiproses = LaporanKerusakan::create([
            'nomor_tiket' => 'INFRA-20261014-9002',
            'warga_id' => $this->wargaA->id,
            'kategori_id' => $kategori->id,
            'kelurahan_id' => $kelurahan->id,
            'judul' => 'Drainase Mampet',
            'deskripsi_kondisi' => 'Air meluap.',
            'latitude' => -6.8910000,
            'longitude' => 107.6110000,
            'status' => 'diproses',
            'prioritas' => 'sedang',
            'foto_bukti_sebelum' => 'laporan/sebelum2.jpg',
        ]);
    }

    public function test_warga_pemilik_dapat_mengirim_umpan_balik_pada_laporan_selesai(): void
    {
        $response = $this->actingAs($this->wargaA)
            ->post(route('warga.laporan.umpan-balik', $this->laporanSelesai->id), [
                'rating' => 5,
                'komentar' => 'Pekerjaan sangat cepat dan aspal jalan mulus kembali. Terima kasih DSDABM!',
                'kecepatan_penanganan' => 5,
                'kualitas_perbaikan' => 5,
            ]);

        $response->assertRedirect(route('warga.laporan.show', $this->laporanSelesai->id));
        $response->assertSessionHas('status');

        $this->assertDatabaseHas('umpan_balik', [
            'laporan_id' => $this->laporanSelesai->id,
            'warga_id' => $this->wargaA->id,
            'rating' => 5,
            'komentar' => 'Pekerjaan sangat cepat dan aspal jalan mulus kembali. Terima kasih DSDABM!',
            'kecepatan_penanganan' => 5,
            'kualitas_perbaikan' => 5,
        ]);

        $this->assertDatabaseHas('riwayat_status', [
            'laporan_id' => $this->laporanSelesai->id,
            'user_id' => $this->wargaA->id,
            'status' => 'selesai',
        ]);
    }

    public function test_warga_tidak_dapat_mengirim_umpan_balik_pada_laporan_yang_belum_selesai(): void
    {
        $response = $this->actingAs($this->wargaA)
            ->post(route('warga.laporan.umpan-balik', $this->laporanDiproses->id), [
                'rating' => 4,
                'komentar' => 'Belum selesai tapi ingin coba rating',
            ]);

        $response->assertSessionHasErrors('status');
        $this->assertDatabaseMissing('umpan_balik', [
            'laporan_id' => $this->laporanDiproses->id,
        ]);
    }

    public function test_warga_lain_tidak_dapat_mengirim_umpan_balik_pada_laporan_orang_lain(): void
    {
        $response = $this->actingAs($this->wargaB)
            ->post(route('warga.laporan.umpan-balik', $this->laporanSelesai->id), [
                'rating' => 1,
                'komentar' => 'Mencoba menilai laporan milik warga A',
            ]);

        $response->assertSessionHasErrors('laporan');
        $this->assertDatabaseMissing('umpan_balik', [
            'warga_id' => $this->wargaB->id,
        ]);
    }

    public function test_warga_tidak_dapat_memberikan_umpan_balik_lebih_dari_satu_kali(): void
    {
        // Berikan feedback pertama
        UmpanBalik::create([
            'laporan_id' => $this->laporanSelesai->id,
            'warga_id' => $this->wargaA->id,
            'rating' => 4,
            'komentar' => 'Evaluasi pertama',
        ]);

        // Coba berikan feedback kedua kali pada laporan yang sama
        $response = $this->actingAs($this->wargaA)
            ->post(route('warga.laporan.umpan-balik', $this->laporanSelesai->id), [
                'rating' => 5,
                'komentar' => 'Mencoba manipulasi rating kedua kali',
            ]);

        $response->assertSessionHasErrors('rating');
        $this->assertEquals(1, UmpanBalik::where('laporan_id', $this->laporanSelesai->id)->count());
    }

    public function test_validasi_rating_wajib_diisi_dan_antara_1_sampai_5(): void
    {
        // 1. Rating kosong
        $responseNull = $this->actingAs($this->wargaA)
            ->post(route('warga.laporan.umpan-balik', $this->laporanSelesai->id), [
                'rating' => '',
            ]);
        $responseNull->assertSessionHasErrors('rating');

        // 2. Rating di luar range (misal: 6 atau 0)
        $responseInvalid = $this->actingAs($this->wargaA)
            ->post(route('warga.laporan.umpan-balik', $this->laporanSelesai->id), [
                'rating' => 6,
            ]);
        $responseInvalid->assertSessionHasErrors('rating');
    }

    public function test_admin_dan_petugas_tidak_dapat_mengirim_umpan_balik_warga(): void
    {
        $responseAdmin = $this->actingAs($this->admin)
            ->post(route('warga.laporan.umpan-balik', $this->laporanSelesai->id), [
                'rating' => 5,
            ]);
        $responseAdmin->assertStatus(403);

        $responsePetugas = $this->actingAs($this->petugas)
            ->post(route('warga.laporan.umpan-balik', $this->laporanSelesai->id), [
                'rating' => 5,
            ]);
        $responsePetugas->assertStatus(403);
    }
}
