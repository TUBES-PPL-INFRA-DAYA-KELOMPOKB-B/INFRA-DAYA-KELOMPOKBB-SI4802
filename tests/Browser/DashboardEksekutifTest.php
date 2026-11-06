<?php

namespace Tests\Browser;

use App\Models\DisposisiPenugasan;
use App\Models\KategoriInfrastruktur;
use App\Models\Kecamatan;
use App\Models\Kelurahan;
use App\Models\LaporanKerusakan;
use App\Models\UmpanBalik;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/**
 * Fase 10: Pengujian E2E Dashboard Eksekutif, Visualisasi Chart.js, dan Metrik Kinerja SLA.
 */
class DashboardEksekutifTest extends DuskTestCase
{
    use DatabaseMigrations;

    private function createSeedData(): array
    {
        $pimpinan = User::factory()->create([
            'name' => 'Bapak Kepala Dinas',
            'email' => 'pimpinan.'.uniqid().'@dsdabm.bandung.go.id',
            'role' => 'pimpinan',
            'is_active' => true,
        ]);

        $admin = User::factory()->create([
            'name' => 'Admin DSDABM',
            'email' => 'admin.'.uniqid().'@dsdabm.bandung.go.id',
            'role' => 'admin',
            'is_active' => true,
        ]);

        $petugas = User::factory()->create([
            'name' => 'Ujang Teknisi',
            'email' => 'ujang.'.uniqid().'@dsdabm.bandung.go.id',
            'role' => 'petugas',
            'is_active' => true,
        ]);

        $warga = User::factory()->create([
            'name' => 'Rina Gunawan',
            'email' => 'rina.'.uniqid().'@test.com',
            'role' => 'warga',
            'is_active' => true,
        ]);

        $kecamatan = Kecamatan::create([
            'kode_kecamatan' => 'BDG-010',
            'nama_kecamatan' => 'Coblong',
        ]);

        $kelurahan = Kelurahan::create([
            'kecamatan_id' => $kecamatan->id,
            'kode_kelurahan' => 'BDG-010-01',
            'nama_kelurahan' => 'Dago',
        ]);

        $kategori = KategoriInfrastruktur::create([
            'nama_kategori' => 'Jalan Rusak',
            'deskripsi' => 'Kerusakan aspal jalan',
            'bobot_prioritas' => 3,
        ]);

        $laporan = LaporanKerusakan::create([
            'nomor_tiket' => 'INFRA-20261014-6666',
            'warga_id' => $warga->id,
            'kategori_id' => $kategori->id,
            'kelurahan_id' => $kelurahan->id,
            'judul' => 'Jalan Berlubang Dekat Simpang',
            'deskripsi_kondisi' => 'Kondisi aspal rusak parah.',
            'latitude' => -6.8900000,
            'longitude' => 107.6100000,
            'status' => 'selesai',
            'prioritas' => 'tinggi',
            'foto_bukti_sebelum' => 'laporan/sebelum.jpg',
            'foto_bukti_sesudah' => 'laporan/sesudah.jpg',
            'catatan_perbaikan' => 'Sudah tuntas diperbaiki tim.',
            'selesai_at' => now(),
        ]);

        DisposisiPenugasan::create([
            'nomor_surat_tugas' => 'SPT-20261014-6666',
            'laporan_id' => $laporan->id,
            'admin_id' => $admin->id,
            'petugas_id' => $petugas->id,
            'tanggal_disposisi' => now()->toDateString(),
            'tenggat_waktu' => now()->addDays(2)->toDateString(),
            'catatan_instruksi' => 'Segera aspal.',
            'status_penugasan' => 'selesai',
        ]);

        UmpanBalik::create([
            'laporan_id' => $laporan->id,
            'warga_id' => $warga->id,
            'rating' => 5,
            'komentar' => 'Penanganan sangat memuaskan.',
        ]);

        return compact('pimpinan', 'admin', 'warga', 'laporan');
    }

    /**
     * Pimpinan dapat mengakses dashboard eksekutif, melihat 6 kartu KPI SLA, dan 4 grafik Chart.js.
     */
    public function test_pimpinan_dapat_melihat_dashboard_eksekutif_metrik_sla_dan_canvas_chart(): void
    {
        $data = $this->createSeedData();

        $this->browse(function (Browser $browser) use ($data) {
            $browser->loginAs($data['pimpinan'])
                ->visit(route('dashboard'))
                ->assertSee('Dashboard Eksekutif')
                ->assertSee('Total Laporan')
                ->assertSee('Tingkat Tuntas')
                ->assertSee('Kepatuhan SLA')
                ->assertSee('Respon Verifikasi')
                ->assertSee('Durasi Perbaikan')
                ->assertSee('Kepuasan Warga')
                ->assertPresent('#monthlyTrendChart')
                ->assertPresent('#categoryDistributionChart')
                ->assertPresent('#topKecamatanChart')
                ->assertPresent('#ratingDistributionChart')
                ->assertSee('Matriks Kinerja per Wilayah Kecamatan')
                ->assertSee('Coblong');
        });
    }

    /**
     * Pimpinan dapat memfilter periode waktu data di dashboard eksekutif.
     */
    public function test_pimpinan_dapat_memfilter_periode_dashboard(): void
    {
        $data = $this->createSeedData();

        $this->browse(function (Browser $browser) use ($data) {
            $browser->loginAs($data['pimpinan'])
                ->visit(route('dashboard'))
                ->select('@filter-periode-days', '30')
                ->pause(500)
                ->assertQueryStringHas('days', '30')
                ->assertSee('Dashboard Eksekutif');
        });
    }

    /**
     * Warga diarahkan ke dashboard khusus warga dan melihat riwayat laporannya.
     */
    public function test_warga_melihat_dashboard_khusus_warga_dan_riwayat_laporan_pribadi(): void
    {
        $data = $this->createSeedData();

        $this->browse(function (Browser $browser) use ($data) {
            $browser->loginAs($data['warga'])
                ->visit(route('dashboard'))
                ->assertTitleContains('Dashboard Warga')
                ->assertSee('Portal Partisipasi Masyarakat')
                ->assertSee('Jalan Berlubang Dekat Simpang')
                ->assertSee('INFRA-20261014-6666');
        });
    }
}
