<?php

namespace Tests\Browser;

use App\Models\DisposisiPenugasan;
use App\Models\KategoriInfrastruktur;
use App\Models\Kecamatan;
use App\Models\Kelurahan;
use App\Models\LaporanKerusakan;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/**
 * Fase 7: Pengujian E2E Pelaksanaan Lapangan oleh Petugas (Navigasi GPS, Mulai Pengerjaan, Foto Bukti Sesudah).
 */
class PetugasTest extends DuskTestCase
{
    use DatabaseMigrations;

    private function createData(): array
    {
        $admin = User::factory()->create([
            'name' => 'Admin DSDABM',
            'email' => 'admin.ptg.'.uniqid().'@dsdabm.bandung.go.id',
            'role' => 'admin',
            'is_active' => true,
        ]);

        $petugas = User::factory()->create([
            'name' => 'Dadang Petugas Lapangan',
            'email' => 'dadang.'.uniqid().'@dsdabm.bandung.go.id',
            'phone' => '081299887766',
            'role' => 'petugas',
            'is_active' => true,
        ]);

        $warga = User::factory()->create([
            'name' => 'Siti Warga',
            'email' => 'siti.'.uniqid().'@test.com',
            'role' => 'warga',
            'is_active' => true,
        ]);

        $kecamatan = Kecamatan::create([
            'kode_kecamatan' => 'BDG-004',
            'nama_kecamatan' => 'Coblong',
        ]);

        $kelurahan = Kelurahan::create([
            'kecamatan_id' => $kecamatan->id,
            'kode_kelurahan' => 'BDG-004-01',
            'nama_kelurahan' => 'Lebak Siliwangi',
        ]);

        $kategori = KategoriInfrastruktur::create([
            'nama_kategori' => 'Jalan Raya',
            'deskripsi' => 'Kerusakan aspal jalan kota',
            'bobot_prioritas' => 3,
        ]);

        $laporan = LaporanKerusakan::create([
            'nomor_tiket' => 'INFRA-20261014-0777',
            'warga_id' => $warga->id,
            'kelurahan_id' => $kelurahan->id,
            'kategori_id' => $kategori->id,
            'judul' => 'Lubang Jalan Besar Depan Sasana Budaya Ganesha',
            'deskripsi_kondisi' => 'Lubang aspal sedalam 15cm berpotensi mengakibatkan kecelakaan pengendara.',
            'latitude' => -6.8872,
            'longitude' => 107.6089,
            'foto_bukti_sebelum' => 'laporan/sebelum/sample.jpg',
            'status' => 'diproses',
            'prioritas' => 'tinggi',
        ]);

        $disposisi = DisposisiPenugasan::create([
            'nomor_surat_tugas' => 'SPT-20261014-9999',
            'laporan_id' => $laporan->id,
            'admin_id' => $admin->id,
            'petugas_id' => $petugas->id,
            'tanggal_disposisi' => now()->toDateString(),
            'tenggat_waktu' => now()->addDays(2)->toDateString(),
            'catatan_instruksi' => 'Segera tambal hotmix dan padatkan dengan baby roller.',
            'status_penugasan' => 'ditugaskan',
        ]);

        return compact('admin', 'petugas', 'warga', 'laporan', 'disposisi');
    }

    /**
     * Petugas dapat melihat daftar tugas penugasan lapangan miliknya.
     */
    public function test_petugas_can_view_tugas_index(): void
    {
        $data = $this->createData();

        $this->browse(function (Browser $browser) use ($data) {
            $browser->loginAs($data['petugas'])
                ->visit(route('petugas.tugas.index'))
                ->assertSee('Daftar Tugas Lapangan')
                ->assertSee('SPT-20261014-9999')
                ->assertSee('Lubang Jalan Besar Depan Sasana Budaya Ganesha')
                ->assertSee('Prioritas Tinggi')
                ->assertPresent("@btn-detail-tugas-{$data['disposisi']->id}");
        });
    }

    /**
     * Petugas dapat membuka lembar kerja detail, melihat peta, dan mengakses tautan navigasi GPS.
     */
    public function test_petugas_can_view_tugas_detail_and_gps_navigation(): void
    {
        $data = $this->createData();

        $this->browse(function (Browser $browser) use ($data) {
            $browser->loginAs($data['petugas'])
                ->visit(route('petugas.tugas.show', $data['disposisi']->id))
                ->assertSee('Lembar Kerja Lapangan')
                ->assertSee('SPT-20261014-9999')
                ->assertSee('Segera tambal hotmix dan padatkan dengan baby roller.')
                ->assertPresent('@btn-navigasi-gmaps')
                ->assertPresent('@btn-navigasi-waze')
                ->assertPresent('#petugasMap')
                ->assertPresent('@btn-mulai-pengerjaan');
        });
    }

    /**
     * Petugas dapat memulai pengerjaan fisik dan melaporkan penyelesaian dengan foto bukti sesudah.
     */
    public function test_petugas_can_start_and_complete_task_with_after_photo(): void
    {
        $data = $this->createData();

        $this->browse(function (Browser $browser) use ($data) {
            // 1. Petugas menekan tombol Mulai Pengerjaan
            $browser->loginAs($data['petugas'])
                ->visit(route('petugas.tugas.show', $data['disposisi']->id))
                ->press('@btn-mulai-pengerjaan')
                ->waitForText('Lapor Penyelesaian Tugas')
                ->assertSee('Pengerjaan Aktif')
                ->assertSee('Lapor Penyelesaian Tugas');

            // 2. Petugas mengunggah foto bukti sesudah dan catatan perbaikan
            $browser->attach('@input-foto-sesudah', __DIR__.'/fixtures/sample_kerusakan.jpg')
                ->type('@input-catatan-perbaikan', 'Penambalan hotmix tuntas dengan ketebalan 5cm, permukaan jalan telah rata kembali dan aman dilalui.')
                ->press('@btn-submit-selesai')
                ->waitForText('Perbaikan Tuntas')
                ->assertSee('Perbaikan Tuntas')
                ->assertSee('Tugas perbaikan lapangan berhasil diselesaikan');
        });
    }

    /**
     * Pengguna selain petugas dilarang mengakses modul tugas lapangan.
     */
    public function test_non_petugas_cannot_access_petugas_tugas(): void
    {
        $data = $this->createData();

        $this->browse(function (Browser $browser) use ($data) {
            $browser->loginAs($data['warga'])
                ->visit(route('petugas.tugas.index'))
                ->assertSee('403');
        });
    }
}
