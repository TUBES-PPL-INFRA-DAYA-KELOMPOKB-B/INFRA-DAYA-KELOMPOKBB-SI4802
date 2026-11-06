<?php

namespace Tests\Feature;

use App\Models\KategoriInfrastruktur;
use App\Models\Kecamatan;
use App\Models\Kelurahan;
use App\Models\LaporanKerusakan;
use App\Models\RiwayatStatus;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VerifikasiAdminTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $warga;
    private Kecamatan $kecamatanCoblong;
    private Kecamatan $kecamatanGedebage;
    private KategoriInfrastruktur $kategoriJalan;
    private KategoriInfrastruktur $kategoriDrainase;
    private LaporanKerusakan $lap1;
    private LaporanKerusakan $lap2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $this->warga = User::factory()->create(['role' => 'warga', 'is_active' => true]);

        $this->kecamatanCoblong = Kecamatan::create([
            'kode_kecamatan' => 'BDG-001',
            'nama_kecamatan' => 'Coblong',
        ]);
        $kelurahanCoblong = Kelurahan::create([
            'kecamatan_id' => $this->kecamatanCoblong->id,
            'kode_kelurahan' => 'BDG-001-01',
            'nama_kelurahan' => 'Dago',
        ]);

        $this->kecamatanGedebage = Kecamatan::create([
            'kode_kecamatan' => 'BDG-002',
            'nama_kecamatan' => 'Gedebage',
        ]);
        $kelurahanGedebage = Kelurahan::create([
            'kecamatan_id' => $this->kecamatanGedebage->id,
            'kode_kelurahan' => 'BDG-002-01',
            'nama_kelurahan' => 'Cisaranten Kidul',
        ]);

        $this->kategoriJalan = KategoriInfrastruktur::create([
            'nama_kategori' => 'Jalan',
            'deskripsi' => 'Kerusakan pada jalan',
            'bobot_prioritas' => 2,
        ]);

        $this->kategoriDrainase = KategoriInfrastruktur::create([
            'nama_kategori' => 'Drainase',
            'deskripsi' => 'Saluran drainase',
            'bobot_prioritas' => 1,
        ]);

        // LAP1: Jalan, Coblong, Menunggu Verifikasi
        $this->lap1 = LaporanKerusakan::create([
            'nomor_tiket' => 'INFRA-001',
            'warga_id' => $this->warga->id,
            'kelurahan_id' => $kelurahanCoblong->id,
            'kategori_id' => $this->kategoriJalan->id,
            'judul' => 'Lubang Jalan Dago Atas',
            'deskripsi_kondisi' => 'Aspal berlubang cukup parah di depan minimarket.',
            'latitude' => -6.8850,
            'longitude' => 107.6150,
            'foto_bukti_sebelum' => 'laporan/sebelum/test1.jpg',
            'status' => 'menunggu_verifikasi',
            'prioritas' => 'sedang',
            'created_at' => Carbon::now()->subDays(2),
        ]);

        // LAP2: Drainase, Gedebage, Diproses
        $this->lap2 = LaporanKerusakan::create([
            'nomor_tiket' => 'INFRA-002',
            'warga_id' => $this->warga->id,
            'kelurahan_id' => $kelurahanGedebage->id,
            'kategori_id' => $this->kategoriDrainase->id,
            'judul' => 'Saluran Drainase Meluap',
            'deskripsi_kondisi' => 'Drainase tersumbat endapan lumpur.',
            'latitude' => -6.9500,
            'longitude' => 107.6800,
            'foto_bukti_sebelum' => 'laporan/sebelum/test2.jpg',
            'status' => 'diproses',
            'prioritas' => 'tinggi',
            'created_at' => Carbon::now()->subDays(5),
        ]);
    }

    // =========================================================================
    // PBI-13 (IFD-14): Daftar Aduan + Filter (TC01-01 s/d TC01-12)
    // =========================================================================

    public function test_tc01_01_admin_dapat_melihat_daftar_aduan(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.verifikasi.index'));

        $response->assertStatus(200);
        $response->assertSee('Daftar Aduan Kerusakan Infrastruktur');
        $response->assertSee('INFRA-001');
        $response->assertSee('INFRA-002');
    }

    public function test_tc01_02_cari_laporan_berdasarkan_nomor_tiket_atau_judul(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.verifikasi.index', ['search' => 'INFRA-001']));

        $response->assertStatus(200);
        $response->assertSee('INFRA-001');
        $response->assertDontSee('INFRA-002');
    }

    public function test_tc01_03_cari_laporan_kata_kunci_tidak_tersedia_menampilkan_pesan_tidak_ditemukan(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.verifikasi.index', ['search' => 'KATA_KUNCI_INVALID_999']));

        $response->assertStatus(200);
        $response->assertSee('Data laporan tidak ditemukan');
        $response->assertDontSee('INFRA-001');
    }

    public function test_tc01_04_filter_berdasarkan_status_laporan(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.verifikasi.index', ['status' => 'menunggu_verifikasi']));

        $response->assertStatus(200);
        $response->assertSee('INFRA-001');
        $response->assertDontSee('INFRA-002');
    }

    public function test_tc01_05_filter_berdasarkan_kategori_infrastruktur(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.verifikasi.index', ['kategori_id' => $this->kategoriJalan->id]));

        $response->assertStatus(200);
        $response->assertSee('INFRA-001');
        $response->assertDontSee('INFRA-002');
    }

    public function test_tc01_06_filter_berdasarkan_kecamatan(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.verifikasi.index', ['kecamatan_id' => $this->kecamatanCoblong->id]));

        $response->assertStatus(200);
        $response->assertSee('INFRA-001');
        $response->assertDontSee('INFRA-002');
    }

    public function test_tc01_07_filter_berdasarkan_periode_tanggal(): void
    {
        $mulai = Carbon::now()->subDays(3)->format('Y-m-d');
        $akhir = Carbon::now()->format('Y-m-d');

        $response = $this->actingAs($this->admin)->get(route('admin.verifikasi.index', [
            'tanggal_mulai' => $mulai,
            'tanggal_akhir' => $akhir,
        ]));

        $response->assertStatus(200);
        $response->assertSee('INFRA-001'); // 2 hari lalu masuk rentang
        $response->assertDontSee('INFRA-002'); // 5 hari lalu di luar rentang
    }

    public function test_tc01_08_kombinasi_beberapa_filter_sekaligus(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.verifikasi.index', [
            'status' => 'menunggu_verifikasi',
            'kecamatan_id' => $this->kecamatanCoblong->id,
        ]));

        $response->assertStatus(200);
        $response->assertSee('INFRA-001');
        $response->assertDontSee('INFRA-002');
    }

    public function test_tc01_09_filter_tidak_ada_hasil_menampilkan_pesan_tidak_ditemukan(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.verifikasi.index', [
            'status' => 'ditolak',
        ]));

        $response->assertStatus(200);
        $response->assertSee('Data laporan tidak ditemukan');
    }

    public function test_tc01_10_tampilkan_seluruh_laporan_tanpa_filter(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.verifikasi.index'));

        $response->assertStatus(200);
        $response->assertSee('INFRA-001');
        $response->assertSee('INFRA-002');
    }

    public function test_tc01_11_guest_tidak_dapat_akses_daftar_aduan_diarahkan_login(): void
    {
        $response = $this->get(route('admin.verifikasi.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_tc01_12_warga_tidak_dapat_akses_daftar_aduan_admin(): void
    {
        $response = $this->actingAs($this->warga)->get(route('admin.verifikasi.index'));

        $response->assertStatus(403);
    }

    public function test_tc01_validasi_tanggal_awal_lebih_besar_dari_akhir(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.verifikasi.index', [
            'tanggal_mulai' => '2026-11-10',
            'tanggal_akhir' => '2026-11-01',
        ]));

        $response->assertSessionHasErrors('tanggal_akhir');
    }
}
