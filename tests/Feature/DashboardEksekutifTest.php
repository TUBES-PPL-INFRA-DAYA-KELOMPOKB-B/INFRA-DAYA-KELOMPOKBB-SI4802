<?php

namespace Tests\Feature;

use App\Models\DisposisiPenugasan;
use App\Models\KategoriInfrastruktur;
use App\Models\Kecamatan;
use App\Models\Kelurahan;
use App\Models\LaporanKerusakan;
use App\Models\RiwayatStatus;
use App\Models\UmpanBalik;
use App\Models\User;
use App\Services\DashboardSlaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardEksekutifTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $pimpinan;

    private User $petugas;

    private User $warga;

    private Kecamatan $kecamatan;

    private Kelurahan $kelurahan;

    private KategoriInfrastruktur $kategori;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $this->pimpinan = User::factory()->create(['role' => 'pimpinan', 'name' => 'Bapak Kepala Dinas', 'is_active' => true]);
        $this->petugas = User::factory()->create(['role' => 'petugas', 'is_active' => true]);
        $this->warga = User::factory()->create(['role' => 'warga', 'is_active' => true]);

        $this->kecamatan = Kecamatan::create(['kode_kecamatan' => 'BDG-01', 'nama_kecamatan' => 'Coblong']);
        $this->kelurahan = Kelurahan::create([
            'kecamatan_id' => $this->kecamatan->id,
            'kode_kelurahan' => 'BDG-01-01',
            'nama_kelurahan' => 'Dago',
        ]);
        $this->kategori = KategoriInfrastruktur::create([
            'nama_kategori' => 'Jalan Rusak',
            'deskripsi' => 'Kerusakan aspal jalan',
            'bobot_prioritas' => 3,
        ]);
    }

    public function test_guest_diarahkan_ke_login_saat_akses_dashboard(): void
    {
        $response = $this->get(route('dashboard'));

        $response->assertRedirect(route('login'));
    }

    public function test_warga_dapat_melihat_dashboard_warga_dengan_metrik_akurat(): void
    {
        LaporanKerusakan::create([
            'nomor_tiket' => 'INFRA-20261014-1111',
            'warga_id' => $this->warga->id,
            'kategori_id' => $this->kategori->id,
            'kelurahan_id' => $this->kelurahan->id,
            'judul' => 'Lubang Jalan Warga',
            'deskripsi_kondisi' => 'Kondisi rusak parah',
            'latitude' => -6.8900000,
            'longitude' => 107.6100000,
            'status' => 'menunggu_verifikasi',
            'prioritas' => 'sedang',
            'foto_bukti_sebelum' => 'laporan/sebelum.jpg',
        ]);

        $response = $this->actingAs($this->warga)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertViewIs('dashboard');
        $response->assertSee('Portal Partisipasi Masyarakat');
        $response->assertSee('Laporan Dikirim');
        $response->assertSee('Lubang Jalan Warga');
    }

    public function test_petugas_diarahkan_ke_antrean_tugas_saat_akses_dashboard(): void
    {
        $response = $this->actingAs($this->petugas)->get(route('dashboard'));

        $response->assertRedirect(route('petugas.tugas.index'));
    }

    public function test_admin_dapat_melihat_dashboard_eksekutif_dan_metrik_sla(): void
    {
        $response = $this->actingAs($this->admin)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertViewIs('dashboard');
        $response->assertSee('Dashboard Eksekutif');
        $response->assertSee('Total Laporan');
        $response->assertSee('Tingkat Tuntas');
        $response->assertSee('Kepatuhan SLA');
        $response->assertSee('Respon Verifikasi');
        $response->assertSee('Durasi Perbaikan');
        $response->assertSee('Kepuasan Warga');
    }

    public function test_pimpinan_dapat_melihat_dashboard_eksekutif_dan_monitoring_sla(): void
    {
        $response = $this->actingAs($this->pimpinan)->get(route('pimpinan.sla.index'));

        $response->assertStatus(200);
        $response->assertViewIs('dashboard');
        $response->assertSee('Dashboard Eksekutif');
        $response->assertSee('Coblong');
    }

    public function test_warga_dilarang_mengakses_route_khusus_pimpinan(): void
    {
        $response = $this->actingAs($this->warga)->get(route('pimpinan.sla.index'));

        $response->assertStatus(403);
    }

    public function test_service_dashboard_menghitung_metrik_sla_dengan_benar(): void
    {
        // 1. Buat Laporan Selesai & Disposisi
        $laporan = LaporanKerusakan::create([
            'nomor_tiket' => 'INFRA-20261014-2222',
            'warga_id' => $this->warga->id,
            'kategori_id' => $this->kategori->id,
            'kelurahan_id' => $this->kelurahan->id,
            'judul' => 'Jalan Rusak Berlubang Tuntas',
            'deskripsi_kondisi' => 'Sudah diperbaiki.',
            'latitude' => -6.8900000,
            'longitude' => 107.6100000,
            'status' => 'selesai',
            'prioritas' => 'tinggi',
            'foto_bukti_sebelum' => 'laporan/sebelum.jpg',
            'foto_bukti_sesudah' => 'laporan/sesudah.jpg',
            'catatan_perbaikan' => 'Sudah mulus kembali.',
            'selesai_at' => now(),
        ]);

        DisposisiPenugasan::create([
            'nomor_surat_tugas' => 'SPT-20261014-2222',
            'laporan_id' => $laporan->id,
            'admin_id' => $this->admin->id,
            'petugas_id' => $this->petugas->id,
            'tanggal_disposisi' => now()->toDateString(),
            'tenggat_waktu' => now()->addDays(2)->toDateString(),
            'catatan_instruksi' => 'Segera selesaikan.',
            'status_penugasan' => 'selesai',
        ]);

        RiwayatStatus::create([
            'laporan_id' => $laporan->id,
            'user_id' => $this->admin->id,
            'status' => 'diproses',
            'keterangan' => 'Diverifikasi admin',
            'created_at' => now()->subHours(4),
        ]);

        UmpanBalik::create([
            'laporan_id' => $laporan->id,
            'warga_id' => $this->warga->id,
            'rating' => 5,
            'komentar' => 'Sangat memuaskan.',
        ]);

        $service = app(DashboardSlaService::class);
        $metrics = $service->getExecutiveMetrics();

        $this->assertEquals(1, $metrics['total_laporan']);
        $this->assertEquals(1, $metrics['selesai']);
        $this->assertEquals(100.0, $metrics['persentase_selesai']);
        $this->assertEquals(100.0, $metrics['kepatuhan_sla']);
        $this->assertEquals(5.0, $metrics['avg_rating']);
        $this->assertEquals(1, $metrics['total_review']);

        $trend = $service->getMonthlyTrendChartData();
        $this->assertCount(6, $trend['labels']);

        $categoryData = $service->getCategoryDistributionChartData();
        $this->assertContains('Jalan Rusak', $categoryData['labels']);

        $topKecamatan = $service->getTopKecamatanChartData();
        $this->assertContains('Coblong', $topKecamatan['labels']);

        $ratings = $service->getRatingDistributionChartData();
        $this->assertEquals(1, $ratings['data'][4]); // Bintang 5 index 4
    }
}
