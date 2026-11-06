<?php

namespace Tests\Feature;

use App\Models\DisposisiPenugasan;
use App\Models\KategoriInfrastruktur;
use App\Models\Kecamatan;
use App\Models\Kelurahan;
use App\Models\LaporanKerusakan;
use App\Models\RiwayatStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublikTrackingAndPetaTest extends TestCase
{
    use RefreshDatabase;

    private User $warga;

    private User $petugas;

    private User $admin;

    private Kecamatan $kecamatanCoblong;

    private Kecamatan $kecamatanCicendo;

    private Kelurahan $kelurahanDago;

    private Kelurahan $kelurahanPasirkaliki;

    private KategoriInfrastruktur $kategoriJalan;

    private KategoriInfrastruktur $kategoriDrainase;

    private LaporanKerusakan $laporanAktif;

    private LaporanKerusakan $laporanSelesai;

    private LaporanKerusakan $laporanDitolak;

    protected function setUp(): void
    {
        parent::setUp();

        $this->warga = User::factory()->create(['role' => 'warga', 'is_active' => true]);
        $this->petugas = User::factory()->create(['role' => 'petugas', 'name' => 'Ahmad Suhendar', 'is_active' => true]);
        $this->admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

        $this->kecamatanCoblong = Kecamatan::create(['kode_kecamatan' => 'BDG-01', 'nama_kecamatan' => 'Coblong']);
        $this->kecamatanCicendo = Kecamatan::create(['kode_kecamatan' => 'BDG-02', 'nama_kecamatan' => 'Cicendo']);

        $this->kelurahanDago = Kelurahan::create([
            'kecamatan_id' => $this->kecamatanCoblong->id,
            'kode_kelurahan' => 'BDG-01-01',
            'nama_kelurahan' => 'Dago',
        ]);
        $this->kelurahanPasirkaliki = Kelurahan::create([
            'kecamatan_id' => $this->kecamatanCicendo->id,
            'kode_kelurahan' => 'BDG-02-01',
            'nama_kelurahan' => 'Pasirkaliki',
        ]);

        $this->kategoriJalan = KategoriInfrastruktur::create([
            'nama_kategori' => 'Jalan Rusak',
            'deskripsi' => 'Kerusakan aspal jalan kota',
            'bobot_prioritas' => 3,
        ]);
        $this->kategoriDrainase = KategoriInfrastruktur::create([
            'nama_kategori' => 'Saluran Air / Drainase',
            'deskripsi' => 'Gorong-gorong tersumbat',
            'bobot_prioritas' => 2,
        ]);

        // 1. Laporan Diproses & Disposisi
        $this->laporanAktif = LaporanKerusakan::create([
            'nomor_tiket' => 'INFRA-20261014-0001',
            'warga_id' => $this->warga->id,
            'kategori_id' => $this->kategoriJalan->id,
            'kelurahan_id' => $this->kelurahanDago->id,
            'judul' => 'Jalan Berlubang di Dekat Simpang Dago',
            'deskripsi_kondisi' => 'Lubang berdiameter 60cm cukup dalam membahayakan pengendara motor.',
            'alamat_patokan' => 'Depan SPBU Dago',
            'latitude' => -6.8850000,
            'longitude' => 107.6130000,
            'status' => 'diproses',
            'prioritas' => 'tinggi',
            'foto_bukti_sebelum' => 'laporan/sebelum_0001.jpg',
        ]);

        DisposisiPenugasan::create([
            'laporan_id' => $this->laporanAktif->id,
            'petugas_id' => $this->petugas->id,
            'admin_id' => $this->admin->id,
            'nomor_surat_tugas' => 'SPT/DSDABM/2026/10/0001',
            'catatan_instruksi' => 'Lakukan penambalan cold-mix segera.',
            'status_penugasan' => 'sedang_dikerjakan',
            'tanggal_disposisi' => now()->toDateString(),
            'tenggat_waktu' => now()->addDays(2)->toDateString(),
        ]);

        RiwayatStatus::create([
            'laporan_id' => $this->laporanAktif->id,
            'user_id' => $this->warga->id,
            'status' => 'menunggu_verifikasi',
            'keterangan' => 'Laporan dibuat oleh warga',
            'created_at' => now()->subHours(2),
        ]);

        RiwayatStatus::create([
            'laporan_id' => $this->laporanAktif->id,
            'user_id' => $this->admin->id,
            'status' => 'diproses',
            'keterangan' => 'Diverifikasi & diterbitkan SPT penugasan ke Ahmad Suhendar',
            'created_at' => now()->subHour(),
        ]);

        // 2. Laporan Selesai
        $this->laporanSelesai = LaporanKerusakan::create([
            'nomor_tiket' => 'INFRA-20261014-0002',
            'warga_id' => $this->warga->id,
            'kategori_id' => $this->kategoriDrainase->id,
            'kelurahan_id' => $this->kelurahanPasirkaliki->id,
            'judul' => 'Drainase Mampet Meluap ke Jalan',
            'deskripsi_kondisi' => 'Sampah menyumbat gorong-gorong air.',
            'alamat_patokan' => 'Jalan Pasirkaliki No. 12',
            'latitude' => -6.9050000,
            'longitude' => 107.6010000,
            'status' => 'selesai',
            'prioritas' => 'sedang',
            'foto_bukti_sebelum' => 'laporan/sebelum_0002.jpg',
            'foto_bukti_sesudah' => 'perbaikan/sesudah_0002.jpg',
            'catatan_perbaikan' => 'Gorong-gorong telah dikeruk dan air mengalir normal kembali.',
            'selesai_at' => now(),
        ]);

        // 3. Laporan Ditolak
        $this->laporanDitolak = LaporanKerusakan::create([
            'nomor_tiket' => 'INFRA-20261014-0003',
            'warga_id' => $this->warga->id,
            'kategori_id' => $this->kategoriJalan->id,
            'kelurahan_id' => $this->kelurahanDago->id,
            'judul' => 'Laporan Fiktif Tanpa Foto Jelas',
            'deskripsi_kondisi' => 'Deskripsi tidak jelas.',
            'alamat_patokan' => 'Jalan Gelap',
            'latitude' => -6.8860000,
            'longitude' => 107.6140000,
            'status' => 'ditolak',
            'prioritas' => 'rendah',
            'foto_bukti_sebelum' => 'laporan/sebelum_0003.jpg',
            'alasan_penolakan' => 'Foto bukti buram dan lokasi bukan kewenangan kota.',
        ]);
    }

    public function test_halaman_lacak_tiket_bisa_diakses_tanpa_login_dan_tanpa_parameter(): void
    {
        $response = $this->get(route('tracking.publik'));

        $response->assertStatus(200);
        $response->assertViewIs('publik.tracking');
        $response->assertSee('Lacak Status & Progres Tiket', false);
        $response->assertSee('Cari Laporan');
    }

    public function test_lacak_tiket_dengan_query_string_valid_menampilkan_detail_laporan(): void
    {
        $response = $this->get(route('tracking.publik', ['nomor_tiket' => 'INFRA-20261014-0001']));

        $response->assertStatus(200);
        $response->assertSee('INFRA-20261014-0001');
        $response->assertSee('Jalan Berlubang di Dekat Simpang Dago');
        $response->assertSee('Jalan Rusak');
        $response->assertSee('Coblong');
        $response->assertSee('Dago');
        $response->assertSee('Ahmad Suhendar');
        $response->assertSee('SPT/DSDABM/2026/10/0001');
        $response->assertSee('Sebelum Perbaikan');
        $response->assertSee('Diverifikasi &amp; diterbitkan SPT penugasan', false);
    }

    public function test_lacak_tiket_dengan_url_path_param_valid_menampilkan_detail_laporan(): void
    {
        $response = $this->get('/lacak-tiket/INFRA-20261014-0001');

        $response->assertStatus(200);
        $response->assertSee('INFRA-20261014-0001');
        $response->assertSee('Jalan Berlubang di Dekat Simpang Dago');
    }

    public function test_lacak_tiket_selesai_menampilkan_foto_sesudah_dan_catatan_perbaikan(): void
    {
        $response = $this->get(route('tracking.publik', ['nomor_tiket' => 'INFRA-20261014-0002']));

        $response->assertStatus(200);
        $response->assertSee('INFRA-20261014-0002');
        $response->assertSee('Tuntas Diperbaiki');
        $response->assertSee('Setelah Perbaikan');
        $response->assertSee('Gorong-gorong telah dikeruk dan air mengalir normal kembali.');
    }

    public function test_lacak_tiket_ditolak_menampilkan_alasan_penolakan(): void
    {
        $response = $this->get(route('tracking.publik', ['nomor_tiket' => 'INFRA-20261014-0003']));

        $response->assertStatus(200);
        $response->assertSee('Laporan Ditolak Verifikator');
        $response->assertSee('Foto bukti buram dan lokasi bukan kewenangan kota.');
    }

    public function test_lacak_tiket_tidak_ditemukan_menampilkan_pesan_tiket_tidak_ditemukan(): void
    {
        $response = $this->get(route('tracking.publik', ['nomor_tiket' => 'INFRA-99999999-9999']));

        $response->assertStatus(200);
        $response->assertSee('Tiket Tidak Ditemukan');
        $response->assertSee('INFRA-99999999-9999');
    }

    public function test_halaman_peta_publik_bisa_diakses_dan_menampilkan_statistik(): void
    {
        $response = $this->get(route('peta.publik'));

        $response->assertStatus(200);
        $response->assertViewIs('publik.peta');
        $response->assertSee('Peta Sebaran Kerusakan Infrastruktur');
        $response->assertSee('Total Masalah Dipetakan');
        $response->assertSee('Coblong');
        $response->assertSee('Cicendo');
        $response->assertSee('Jalan Rusak');
    }

    public function test_api_markers_mengembalikan_json_daftar_marker_valid(): void
    {
        $response = $this->getJson(route('api.peta.markers'));

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'count',
                'data' => [
                    '*' => [
                        'id',
                        'nomor_tiket',
                        'judul',
                        'latitude',
                        'longitude',
                        'status',
                        'prioritas',
                        'kategori',
                        'kelurahan',
                        'kecamatan',
                        'foto_sebelum',
                        'foto_sesudah',
                        'tanggal',
                        'url_lacak',
                    ],
                ],
            ]);

        $this->assertEquals(3, $response->json('count'));
    }

    public function test_api_markers_bisa_difilter_berdasarkan_status(): void
    {
        $response = $this->getJson(route('api.peta.markers', ['status' => 'selesai']));

        $response->assertStatus(200);
        $this->assertEquals(1, $response->json('count'));
        $this->assertEquals('INFRA-20261014-0002', $response->json('data.0.nomor_tiket'));
    }

    public function test_api_markers_bisa_difilter_berdasarkan_kategori(): void
    {
        $response = $this->getJson(route('api.peta.markers', ['kategori_id' => $this->kategoriDrainase->id]));

        $response->assertStatus(200);
        $this->assertEquals(1, $response->json('count'));
        $this->assertEquals('INFRA-20261014-0002', $response->json('data.0.nomor_tiket'));
    }

    public function test_api_markers_bisa_difilter_berdasarkan_kecamatan(): void
    {
        $response = $this->getJson(route('api.peta.markers', ['kecamatan_id' => $this->kecamatanCoblong->id]));

        $response->assertStatus(200);
        $this->assertEquals(2, $response->json('count'));
        $nomorTiketList = collect($response->json('data'))->pluck('nomor_tiket')->all();
        $this->assertContains('INFRA-20261014-0001', $nomorTiketList);
        $this->assertContains('INFRA-20261014-0003', $nomorTiketList);
    }

    public function test_api_markers_bisa_difilter_berdasarkan_kata_kunci(): void
    {
        $response = $this->getJson(route('api.peta.markers', ['search' => 'Simpang Dago']));

        $response->assertStatus(200);
        $this->assertEquals(1, $response->json('count'));
        $this->assertEquals('INFRA-20261014-0001', $response->json('data.0.nomor_tiket'));
    }
}
