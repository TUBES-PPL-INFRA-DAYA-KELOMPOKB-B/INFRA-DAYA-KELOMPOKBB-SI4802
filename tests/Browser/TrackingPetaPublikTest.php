<?php

namespace Tests\Browser;

use App\Models\DisposisiPenugasan;
use App\Models\KategoriInfrastruktur;
use App\Models\Kecamatan;
use App\Models\Kelurahan;
use App\Models\LaporanKerusakan;
use App\Models\RiwayatStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/**
 * Fase 8: Pengujian E2E Pelacakan Tiket Publik, Visualisasi Before-After, Geospasial Leaflet, dan Peta Sebaran.
 */
class TrackingPetaPublikTest extends DuskTestCase
{
    use DatabaseMigrations;

    private function createSeedData(): array
    {
        $warga = User::factory()->create([
            'name' => 'Warga Bandung Juara',
            'email' => 'warga.tracking.'.uniqid().'@test.com',
            'role' => 'warga',
            'is_active' => true,
        ]);

        $admin = User::factory()->create([
            'name' => 'Admin DSDABM Pusat',
            'email' => 'admin.tracking.'.uniqid().'@dsdabm.bandung.go.id',
            'role' => 'admin',
            'is_active' => true,
        ]);

        $petugas = User::factory()->create([
            'name' => 'Cecep Surveyor',
            'email' => 'cecep.'.uniqid().'@dsdabm.bandung.go.id',
            'role' => 'petugas',
            'is_active' => true,
        ]);

        $kecamatanCoblong = Kecamatan::create([
            'kode_kecamatan' => 'BDG-005',
            'nama_kecamatan' => 'Coblong',
        ]);

        $kelurahanDago = Kelurahan::create([
            'kecamatan_id' => $kecamatanCoblong->id,
            'kode_kelurahan' => 'BDG-005-01',
            'nama_kelurahan' => 'Dago',
        ]);

        $kategori = KategoriInfrastruktur::create([
            'nama_kategori' => 'Jalan Rusak & Berlubang',
            'deskripsi' => 'Kerusakan aspal jalan perkotaan',
            'bobot_prioritas' => 3,
        ]);

        $laporan = LaporanKerusakan::create([
            'nomor_tiket' => 'INFRA-20261014-8888',
            'warga_id' => $warga->id,
            'kategori_id' => $kategori->id,
            'kelurahan_id' => $kelurahanDago->id,
            'judul' => 'Aspal Ambles di Dekat ITB Dago',
            'deskripsi_kondisi' => 'Amblesan jalan sekitar 1 meter membahayakan lalu lintas.',
            'alamat_patokan' => 'Sebelah Barat Gerbang ITB Dago',
            'latitude' => -6.8915000,
            'longitude' => 107.6106000,
            'status' => 'selesai',
            'prioritas' => 'tinggi',
            'foto_bukti_sebelum' => 'laporan/sebelum/test_ambles.jpg',
            'foto_bukti_sesudah' => 'laporan/sesudah/test_ambles_fixed.jpg',
            'catatan_perbaikan' => 'Sudah dilakukan pengurukan base course dan pengaspalan hotmix tuntas.',
            'selesai_at' => now(),
        ]);

        DisposisiPenugasan::create([
            'nomor_surat_tugas' => 'SPT-20261014-8888',
            'laporan_id' => $laporan->id,
            'admin_id' => $admin->id,
            'petugas_id' => $petugas->id,
            'tanggal_disposisi' => now()->toDateString(),
            'tenggat_waktu' => now()->addDays(2)->toDateString(),
            'catatan_instruksi' => 'Segera aspal ulang lokasi tersebut.',
            'status_penugasan' => 'selesai',
        ]);

        RiwayatStatus::create([
            'laporan_id' => $laporan->id,
            'user_id' => $warga->id,
            'status' => 'menunggu_verifikasi',
            'keterangan' => 'Laporan kerusakan dikirimkan oleh warga',
            'created_at' => now()->subHours(5),
        ]);

        RiwayatStatus::create([
            'laporan_id' => $laporan->id,
            'user_id' => $admin->id,
            'status' => 'diproses',
            'keterangan' => 'Diverifikasi admin dan diterbitkan SPT-20261014-8888',
            'created_at' => now()->subHours(3),
        ]);

        RiwayatStatus::create([
            'laporan_id' => $laporan->id,
            'user_id' => $petugas->id,
            'status' => 'selesai',
            'keterangan' => 'Pengerjaan lapangan telah selesai dan foto sesudah diunggah',
            'created_at' => now()->subHour(),
        ]);

        return compact('warga', 'admin', 'petugas', 'laporan');
    }

    /**
     * Warga dapat melacak status tiket publik, melihat foto before-after, peta geospasial, dan audit timeline.
     */
    public function test_warga_dapat_mencari_dan_melacak_tiket_laporan_hingga_melihat_dokumentasi_dan_timeline(): void
    {
        $data = $this->createSeedData();
        $nomorTiket = $data['laporan']->nomor_tiket;

        $this->browse(function (Browser $browser) use ($nomorTiket) {
            $browser->visit(route('tracking.publik'))
                ->assertSee('Lacak Status & Progres Tiket')
                ->type('@input-lacak-tiket', $nomorTiket)
                ->click('@btn-submit-lacak')
                ->waitForText($nomorTiket)
                ->assertSee($nomorTiket)
                ->assertSee('Aspal Ambles di Dekat ITB Dago')
                ->assertSee('SPT-20261014-8888')
                ->assertSee('Cecep Surveyor')
                ->assertSee('Sebelum Perbaikan')
                ->assertSee('Setelah Perbaikan')
                ->assertSee('Sudah dilakukan pengurukan base course dan pengaspalan hotmix tuntas.')
                ->assertPresent('#trackingMap')
                ->assertSee('Jejak Transparansi')
                ->assertSee('Diverifikasi admin dan diterbitkan SPT-20261014-8888');
        });
    }

    /**
     * Pelacakan dengan nomor tiket yang tidak terdaftar menampilkan kartu informasi error yang jelas.
     */
    public function test_warga_mencari_nomor_tiket_tidak_valid_melihat_pesan_tiket_tidak_ditemukan(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(route('tracking.publik'))
                ->type('@input-lacak-tiket', 'INFRA-99999999-0000')
                ->click('@btn-submit-lacak')
                ->waitForText('Tiket Tidak Ditemukan')
                ->assertSee('Tiket Tidak Ditemukan')
                ->assertSee('INFRA-99999999-0000')
                ->assertSee('Ulangi Pencarian');
        });
    }

    /**
     * Halaman peta sebaran publik dapat dibuka dan menampilkan kontrol filter serta leaflet map.
     */
    public function test_publik_dapat_melihat_halaman_peta_interaktif_dan_statistik(): void
    {
        $this->createSeedData();

        $this->browse(function (Browser $browser) {
            $browser->visit(route('peta.publik'))
                ->assertSee('Peta Sebaran Kerusakan Infrastruktur')
                ->assertSee('Total Masalah Dipetakan')
                ->assertSee('Tuntas Diperbaiki')
                ->assertPresent('#leafletPublicMap')
                ->assertPresent('@filter-kategori')
                ->assertPresent('@filter-status')
                ->assertPresent('@filter-kecamatan')
                ->assertPresent('@filter-search')
                ->waitForText('titik kerusakan', 10)
                ->assertSee('titik kerusakan');
        });
    }
}
