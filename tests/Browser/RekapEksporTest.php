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
 * Fase 11: Pengujian E2E Rekapitulasi Data, Filter Multi-Kriteria, dan Ekspor Excel/PDF.
 */
class RekapEksporTest extends DuskTestCase
{
    use DatabaseMigrations;

    private function createSeedData(): array
    {
        $admin = User::factory()->create([
            'name' => 'Admin Rekap DSDABM',
            'email' => 'admin.'.uniqid().'@dsdabm.bandung.go.id',
            'role' => 'admin',
            'is_active' => true,
        ]);

        $pimpinan = User::factory()->create([
            'name' => 'Kepala Dinas DSDABM',
            'email' => 'pimpinan.'.uniqid().'@dsdabm.bandung.go.id',
            'role' => 'pimpinan',
            'is_active' => true,
        ]);

        $petugas = User::factory()->create([
            'name' => 'Asep Lapangan',
            'email' => 'asep.'.uniqid().'@dsdabm.bandung.go.id',
            'role' => 'petugas',
            'is_active' => true,
        ]);

        $warga = User::factory()->create([
            'name' => 'Iwan Fals',
            'email' => 'iwan.'.uniqid().'@warga.com',
            'role' => 'warga',
            'is_active' => true,
        ]);

        $kecCoblong = Kecamatan::create([
            'kode_kecamatan' => 'BDG-01',
            'nama_kecamatan' => 'Coblong',
        ]);

        $kecCicendo = Kecamatan::create([
            'kode_kecamatan' => 'BDG-02',
            'nama_kecamatan' => 'Cicendo',
        ]);

        $kelDago = Kelurahan::create([
            'kecamatan_id' => $kecCoblong->id,
            'kode_kelurahan' => 'BDG-01-01',
            'nama_kelurahan' => 'Dago',
        ]);

        $kelPasirkaliki = Kelurahan::create([
            'kecamatan_id' => $kecCicendo->id,
            'kode_kelurahan' => 'BDG-02-01',
            'nama_kelurahan' => 'Pasirkaliki',
        ]);

        $katJalan = KategoriInfrastruktur::create([
            'nama_kategori' => 'Jalan Rusak',
            'bobot_prioritas' => 3,
        ]);

        $katDrainase = KategoriInfrastruktur::create([
            'nama_kategori' => 'Saluran Drainase',
            'bobot_prioritas' => 2,
        ]);

        // Laporan 1: Selesai di Coblong
        $laporan1 = LaporanKerusakan::create([
            'nomor_tiket' => 'INFRA-COBLONG-001',
            'warga_id' => $warga->id,
            'kelurahan_id' => $kelDago->id,
            'kategori_id' => $katJalan->id,
            'judul' => 'Aspal Retak Parah Jalan Dago',
            'deskripsi_kondisi' => 'Retakan memanjang lebih dari 10 meter mengganggu pengendara.',
            'latitude' => -6.885000,
            'longitude' => 107.613000,
            'foto_bukti_sebelum' => 'laporan/sebelum/sample1.jpg',
            'foto_bukti_sesudah' => 'laporan/sesudah/sample1.jpg',
            'status' => 'selesai',
            'prioritas' => 'tinggi',
            'selesai_at' => now(),
        ]);

        DisposisiPenugasan::create([
            'nomor_surat_tugas' => 'SPT-20261014-0001',
            'laporan_id' => $laporan1->id,
            'admin_id' => $admin->id,
            'petugas_id' => $petugas->id,
            'status_penugasan' => 'selesai',
            'tanggal_disposisi' => now()->toDateString(),
            'tenggat_waktu' => now()->addDays(3)->toDateString(),
            'catatan_instruksi' => 'Lakukan penambalan aspal segera.',
        ]);

        UmpanBalik::create([
            'laporan_id' => $laporan1->id,
            'warga_id' => $warga->id,
            'rating' => 5,
            'kecepatan_penanganan' => 5,
            'kualitas_perbaikan' => 5,
            'komentar' => 'Jalanan sudah mulus kembali!',
        ]);

        // Laporan 2: Diproses di Cicendo
        $laporan2 = LaporanKerusakan::create([
            'nomor_tiket' => 'INFRA-CICENDO-002',
            'warga_id' => $warga->id,
            'kelurahan_id' => $kelPasirkaliki->id,
            'kategori_id' => $katDrainase->id,
            'judul' => 'Gorong-gorong Meluap Pasirkaliki',
            'deskripsi_kondisi' => 'Genangan air menutupi sebagian badan jalan.',
            'latitude' => -6.902000,
            'longitude' => 107.601000,
            'foto_bukti_sebelum' => 'laporan/sebelum/sample2.jpg',
            'status' => 'diproses',
            'prioritas' => 'sedang',
        ]);

        return compact('admin', 'pimpinan', 'petugas', 'warga', 'kecCoblong', 'kecCicendo', 'laporan1', 'laporan2');
    }

    /**
     * Uji Admin dapat membuka halaman rekap, melihat KPI, tombol ekspor, dan daftar laporan.
     */
    public function test_admin_dapat_mengakses_rekap_melihat_kpi_dan_tombol_ekspor(): void
    {
        $data = $this->createSeedData();

        $this->browse(function (Browser $browser) use ($data) {
            $browser->loginAs($data['admin'])
                ->visit('/admin/rekap')
                ->waitForText('Rekapitulasi & Ekspor Laporan')
                ->assertSee('Admin DSDABM')
                ->assertSee('Fase 11')
                ->assertSee('Total Sesuai Filter')
                ->assertSee('Selesai Diperbaiki')
                ->assertSee('Sedang Diproses')
                ->assertSee('Kepuasan Warga')
                ->assertVisible('@btn-export-excel')
                ->assertVisible('@btn-export-pdf')
                ->assertSee('INFRA-COBLONG-001')
                ->assertSee('Aspal Retak Parah Jalan Dago')
                ->assertSee('INFRA-CICENDO-002')
                ->assertSee('Gorong-gorong Meluap Pasirkaliki');
        });
    }

    /**
     * Uji Admin dapat memfilter laporan multi-kriteria (Kecamatan dan Status).
     */
    public function test_admin_dapat_memfilter_laporan_multi_kriteria(): void
    {
        $data = $this->createSeedData();

        $this->browse(function (Browser $browser) use ($data) {
            $browser->loginAs($data['admin'])
                ->visit('/admin/rekap')
                ->waitForText('Rekapitulasi & Ekspor Laporan')
                ->select('#kecamatan_id', (string) $data['kecCoblong']->id)
                ->select('#status', 'selesai')
                ->click('@btn-terapkan-filter')
                ->pause(500)
                ->assertSee('INFRA-COBLONG-001')
                ->assertSee('Aspal Retak Parah Jalan Dago')
                ->assertDontSee('INFRA-CICENDO-002');
        });
    }

    /**
     * Uji Pimpinan Eksekutif dapat mengakses halaman rekap dan melihat indikator eksekutif.
     */
    public function test_pimpinan_dapat_mengakses_halaman_rekap_eksekutif(): void
    {
        $data = $this->createSeedData();

        $this->browse(function (Browser $browser) use ($data) {
            $browser->loginAs($data['pimpinan'])
                ->visit('/pimpinan/rekap')
                ->waitForText('Rekapitulasi & Ekspor Laporan')
                ->assertSee('Pimpinan Eksekutif')
                ->assertVisible('@btn-export-excel')
                ->assertVisible('@btn-export-pdf')
                ->assertSee('INFRA-COBLONG-001');
        });
    }
}
