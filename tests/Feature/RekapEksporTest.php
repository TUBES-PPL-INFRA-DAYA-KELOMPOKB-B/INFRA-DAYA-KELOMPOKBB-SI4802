<?php

namespace Tests\Feature;

use App\Models\DisposisiPenugasan;
use App\Models\KategoriInfrastruktur;
use App\Models\Kecamatan;
use App\Models\Kelurahan;
use App\Models\LaporanKerusakan;
use App\Models\UmpanBalik;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RekapEksporTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $pimpinan;

    protected User $warga;

    protected User $petugas;

    protected Kecamatan $kecCoblong;

    protected Kecamatan $kecCicendo;

    protected Kelurahan $kelDago;

    protected Kelurahan $kelPasirkaliki;

    protected KategoriInfrastruktur $katJalan;

    protected KategoriInfrastruktur $katDrainase;

    protected function setUp(): void
    {
        parent::setUp();

        // Buat User Roles
        $this->admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->pimpinan = User::factory()->create([
            'role' => 'pimpinan',
            'is_active' => true,
        ]);

        $this->warga = User::factory()->create([
            'role' => 'warga',
            'is_active' => true,
        ]);

        $this->petugas = User::factory()->create([
            'role' => 'petugas',
            'is_active' => true,
        ]);

        // Buat Master Data Wilayah & Kategori
        $this->kecCoblong = Kecamatan::factory()->create(['nama_kecamatan' => 'Coblong']);
        $this->kecCicendo = Kecamatan::factory()->create(['nama_kecamatan' => 'Cicendo']);

        $this->kelDago = Kelurahan::factory()->create([
            'kecamatan_id' => $this->kecCoblong->id,
            'nama_kelurahan' => 'Dago',
        ]);

        $this->kelPasirkaliki = Kelurahan::factory()->create([
            'kecamatan_id' => $this->kecCicendo->id,
            'nama_kelurahan' => 'Pasirkaliki',
        ]);

        $this->katJalan = KategoriInfrastruktur::factory()->create(['nama_kategori' => 'Jalan Rusak']);
        $this->katDrainase = KategoriInfrastruktur::factory()->create(['nama_kategori' => 'Saluran Drainase']);
    }

    public function test_tamu_tidak_bisa_mengakses_rekap_dan_diredirect_ke_login(): void
    {
        $this->get(route('admin.rekap.index'))->assertRedirect(route('login'));
        $this->get(route('admin.rekap.excel'))->assertRedirect(route('login'));
        $this->get(route('admin.rekap.pdf'))->assertRedirect(route('login'));
        $this->get(route('pimpinan.rekap.index'))->assertRedirect(route('login'));
    }

    public function test_warga_dan_petugas_dilarang_mengakses_rekap(): void
    {
        $this->actingAs($this->warga)->get(route('admin.rekap.index'))->assertForbidden();
        $this->actingAs($this->warga)->get(route('admin.rekap.excel'))->assertForbidden();
        $this->actingAs($this->warga)->get(route('pimpinan.rekap.index'))->assertForbidden();

        $this->actingAs($this->petugas)->get(route('admin.rekap.index'))->assertForbidden();
        $this->actingAs($this->petugas)->get(route('admin.rekap.pdf'))->assertForbidden();
        $this->actingAs($this->petugas)->get(route('pimpinan.rekap.index'))->assertForbidden();
    }

    public function test_admin_dapat_melihat_halaman_rekap_dengan_pratinjau_dan_statistik(): void
    {
        $laporan1 = LaporanKerusakan::factory()->create([
            'nomor_tiket' => 'INFRA-20261014-0001',
            'judul' => 'Jalan Berlubang Parah Dago',
            'kelurahan_id' => $this->kelDago->id,
            'kategori_id' => $this->katJalan->id,
            'warga_id' => $this->warga->id,
            'status' => 'selesai',
            'prioritas' => 'tinggi',
            'selesai_at' => now(),
        ]);

        UmpanBalik::create([
            'laporan_id' => $laporan1->id,
            'warga_id' => $this->warga->id,
            'rating' => 5,
            'kecepatan_penanganan' => 5,
            'kualitas_perbaikan' => 5,
            'komentar' => 'Penanganan sangat cepat dan rapi!',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.rekap.index'));

        $response->assertOk();
        $response->assertSee('Rekapitulasi & Ekspor Laporan');
        $response->assertSee('INFRA-20261014-0001');
        $response->assertSee('Jalan Berlubang Parah Dago');
        $response->assertSee('Coblong');
        $response->assertSee('Unduh Excel (.xlsx)');
        $response->assertSee('Cetak PDF (.pdf)');
    }

    public function test_pimpinan_dapat_melihat_halaman_rekap_eksekutif(): void
    {
        $response = $this->actingAs($this->pimpinan)->get(route('pimpinan.rekap.index'));

        $response->assertOk();
        $response->assertSee('Pimpinan Eksekutif');
        $response->assertSee('Rekapitulasi & Ekspor Laporan');
    }

    public function test_filter_rekap_berdasarkan_rentang_tanggal(): void
    {
        // Laporan di luar rentang (September 2026)
        LaporanKerusakan::factory()->create([
            'nomor_tiket' => 'INFRA-20260901-0001',
            'kelurahan_id' => $this->kelDago->id,
            'kategori_id' => $this->katJalan->id,
            'warga_id' => $this->warga->id,
            'created_at' => '2026-09-01 10:00:00',
        ]);

        // Laporan di dalam rentang (Oktober 2026)
        LaporanKerusakan::factory()->create([
            'nomor_tiket' => 'INFRA-20261010-0002',
            'kelurahan_id' => $this->kelDago->id,
            'kategori_id' => $this->katJalan->id,
            'warga_id' => $this->warga->id,
            'created_at' => '2026-10-10 10:00:00',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.rekap.index', [
            'tanggal_mulai' => '2026-10-01',
            'tanggal_akhir' => '2026-10-31',
        ]));

        $response->assertOk();
        $response->assertSee('INFRA-20261010-0002');
        $response->assertDontSee('INFRA-20260901-0001');
    }

    public function test_filter_rekap_berdasarkan_kecamatan(): void
    {
        // Laporan di Coblong
        LaporanKerusakan::factory()->create([
            'nomor_tiket' => 'INFRA-COBLONG-001',
            'kelurahan_id' => $this->kelDago->id,
            'kategori_id' => $this->katJalan->id,
            'warga_id' => $this->warga->id,
        ]);

        // Laporan di Cicendo
        LaporanKerusakan::factory()->create([
            'nomor_tiket' => 'INFRA-CICENDO-002',
            'kelurahan_id' => $this->kelPasirkaliki->id,
            'kategori_id' => $this->katJalan->id,
            'warga_id' => $this->warga->id,
        ]);

        // Filter Coblong
        $response = $this->actingAs($this->admin)->get(route('admin.rekap.index', [
            'kecamatan_id' => $this->kecCoblong->id,
        ]));

        $response->assertOk();
        $response->assertSee('INFRA-COBLONG-001');
        $response->assertDontSee('INFRA-CICENDO-002');
    }

    public function test_filter_rekap_berdasarkan_status_dan_kategori(): void
    {
        // Laporan Selesai Drainase
        LaporanKerusakan::factory()->create([
            'nomor_tiket' => 'INFRA-DRAINASE-SELESAI',
            'kelurahan_id' => $this->kelDago->id,
            'kategori_id' => $this->katDrainase->id,
            'status' => 'selesai',
            'warga_id' => $this->warga->id,
        ]);

        // Laporan Diproses Jalan
        LaporanKerusakan::factory()->create([
            'nomor_tiket' => 'INFRA-JALAN-PROSES',
            'kelurahan_id' => $this->kelDago->id,
            'kategori_id' => $this->katJalan->id,
            'status' => 'diproses',
            'warga_id' => $this->warga->id,
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.rekap.index', [
            'status' => 'selesai',
            'kategori_id' => $this->katDrainase->id,
        ]));

        $response->assertOk();
        $response->assertSee('INFRA-DRAINASE-SELESAI');
        $response->assertDontSee('INFRA-JALAN-PROSES');
    }

    public function test_filter_pencarian_kata_kunci(): void
    {
        LaporanKerusakan::factory()->create([
            'nomor_tiket' => 'INFRA-CARI-UNIK-999',
            'judul' => 'Jembatan Amblas Dekat Pasar',
            'kelurahan_id' => $this->kelDago->id,
            'kategori_id' => $this->katJalan->id,
            'warga_id' => $this->warga->id,
        ]);

        LaporanKerusakan::factory()->create([
            'nomor_tiket' => 'INFRA-LAIN-111',
            'judul' => 'Trotoar Patah',
            'kelurahan_id' => $this->kelDago->id,
            'kategori_id' => $this->katJalan->id,
            'warga_id' => $this->warga->id,
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.rekap.index', [
            'search' => 'Amblas',
        ]));

        $response->assertOk();
        $response->assertSee('INFRA-CARI-UNIK-999');
        $response->assertDontSee('INFRA-LAIN-111');
    }

    public function test_admin_dan_pimpinan_dapat_mengunduh_ekspor_excel(): void
    {
        $laporan = LaporanKerusakan::factory()->create([
            'nomor_tiket' => 'INFRA-EXCEL-001',
            'judul' => 'Laporan Uji Excel DSDABM',
            'kelurahan_id' => $this->kelDago->id,
            'kategori_id' => $this->katJalan->id,
            'warga_id' => $this->warga->id,
            'status' => 'selesai',
            'prioritas' => 'tinggi',
            'selesai_at' => now(),
        ]);

        DisposisiPenugasan::factory()->create([
            'laporan_id' => $laporan->id,
            'petugas_id' => $this->petugas->id,
            'status_penugasan' => 'selesai',
        ]);

        // Uji ekspor excel oleh Admin
        $responseAdmin = $this->actingAs($this->admin)->get(route('admin.rekap.excel'));
        $responseAdmin->assertOk();
        $this->assertStringContainsString('rekap-laporan-dsdabm-', $responseAdmin->headers->get('content-disposition') ?? '');
        $this->assertStringContainsString('.xlsx', $responseAdmin->headers->get('content-disposition') ?? '');

        // Uji ekspor excel oleh Pimpinan
        $responsePimpinan = $this->actingAs($this->pimpinan)->get(route('pimpinan.rekap.excel'));
        $responsePimpinan->assertOk();
        $this->assertStringContainsString('.xlsx', $responsePimpinan->headers->get('content-disposition') ?? '');
    }

    public function test_admin_dan_pimpinan_dapat_mengunduh_ekspor_pdf(): void
    {
        LaporanKerusakan::factory()->create([
            'nomor_tiket' => 'INFRA-PDF-001',
            'judul' => 'Laporan Uji PDF DSDABM',
            'kelurahan_id' => $this->kelDago->id,
            'kategori_id' => $this->katJalan->id,
            'warga_id' => $this->warga->id,
            'status' => 'diproses',
            'prioritas' => 'tinggi',
        ]);

        // Stream mode (default)
        $responseStream = $this->actingAs($this->admin)->get(route('admin.rekap.pdf'));
        $responseStream->assertOk();
        $responseStream->assertHeader('content-type', 'application/pdf');

        // Download mode
        $responseDownload = $this->actingAs($this->admin)->get(route('admin.rekap.pdf', ['download' => 1]));
        $responseDownload->assertOk();
        $responseDownload->assertHeader('content-type', 'application/pdf');
        $this->assertStringContainsString('attachment', $responseDownload->headers->get('content-disposition') ?? '');
        $this->assertStringContainsString('.pdf', $responseDownload->headers->get('content-disposition') ?? '');

        // Pimpinan PDF route
        $responsePimpinan = $this->actingAs($this->pimpinan)->get(route('pimpinan.rekap.pdf'));
        $responsePimpinan->assertOk();
        $responsePimpinan->assertHeader('content-type', 'application/pdf');
    }
}
