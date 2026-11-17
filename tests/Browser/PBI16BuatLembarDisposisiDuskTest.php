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

class PBI16BuatLembarDisposisiDuskTest extends DuskTestCase
{
    use DatabaseMigrations;

    private function setupData(): array
    {
        $admin = User::factory()->create([
            'name' => 'Admin DSDABM',
            'email' => 'admin.pbi16.' . uniqid() . '@bandung.go.id',
            'role' => 'admin',
            'is_active' => true,
        ]);

        $petugas = User::factory()->create([
            'name' => 'Dadang Petugas Lapangan',
            'email' => 'dadang.' . uniqid() . '@bandung.go.id',
            'phone' => '081234567890',
            'role' => 'petugas',
            'is_active' => true,
        ]);

        $kecamatan = Kecamatan::create([
            'kode_kecamatan' => 'BDG-099',
            'nama_kecamatan' => 'Bandung Wetan',
        ]);

        $kelurahan = Kelurahan::create([
            'kecamatan_id' => $kecamatan->id,
            'kode_kelurahan' => 'BDG-099-01',
            'nama_kelurahan' => 'Tamansari',
        ]);

        $kategori = KategoriInfrastruktur::create([
            'nama_kategori' => 'Jalan Rusak',
            'deskripsi' => 'Kerusakan jalan aspal',
            'bobot_prioritas' => 3,
        ]);

        $laporan = LaporanKerusakan::create([
            'nomor_tiket' => 'INFRA-DUSK-0001',
            'warga_id' => $admin->id,
            'kelurahan_id' => $kelurahan->id,
            'kategori_id' => $kategori->id,
            'judul' => 'Lubang Dalam di Jalan Tamansari',
            'deskripsi_kondisi' => 'Lubang jalan sedalam 25 cm membahayakan pengendara.',
            'latitude' => -6.8990,
            'longitude' => 107.6080,
            'foto_bukti_sebelum' => 'laporan/sebelum/sample.jpg',
            'status' => 'diproses',
            'prioritas' => 'tinggi',
        ]);

        return compact('admin', 'petugas', 'laporan');
    }

    /**
     * E2E: Admin dapat melihat daftar laporan siap disposisi, melakukan pencarian, dan membuat disposisi.
     */
    public function test_e2e_admin_mencari_laporan_dan_membuat_disposisi(): void
    {
        $data = $this->setupData();

        $this->browse(function (Browser $browser) use ($data) {
            $browser->loginAs($data['admin'])
                // 1. Kunjungi index disposisi: Memuat bagian Laporan Siap Disposisi
                ->visit(route('admin.disposisi.index'))
                ->assertSee('Daftar Laporan Siap Disposisi')
                ->assertSee($data['laporan']->nomor_tiket)
                ->assertSee($data['laporan']->judul)
                ->assertPresent('@btn-buat-disposisi-' . $data['laporan']->id)

                // 2. Klik Buat Disposisi untuk laporan tersebut
                ->click('@btn-buat-disposisi-' . $data['laporan']->id)
                ->assertPathIs('/admin/disposisi/create')
                ->assertSee('Terbitkan Surat Perintah Tugas (SPT)')
                ->assertSee('Ringkasan Laporan Terpilih')
                ->assertSee($data['laporan']->judul)
                ->assertSee($data['laporan']->deskripsi_kondisi)

                // 3. Pilih petugas aktif dan isi instruksi
                ->select('@select-petugas-id', (string) $data['petugas']->id)
                ->type('@input-catatan-instruksi', 'Lakukan pengukuran dimensi lubang jalan dan tambal dengan coldmix sebelum sore hari.')
                ->press('@btn-submit-disposisi')

                // 4. Diarahkan ke halaman detail disposisi
                ->waitForText('Surat Perintah Tugas')
                ->assertSee('berhasil diterbitkan')
                ->assertSee($data['petugas']->name)
                ->assertSee('Lakukan pengukuran dimensi lubang jalan')
                ->assertSee('Ditugaskan')

                // 5. Verifikasi laporan sudah keluar dari daftar siap disposisi
                ->visit(route('admin.disposisi.index'))
                ->assertSee('Belum ada laporan yang dapat didisposisikan');
        });
    }

    /**
     * E2E: Tombol Batal pada formulir pembuatan disposisi mengembalikan pengguna ke daftar tanpa menyimpan.
     */
    public function test_e2e_tombol_batal_kembali_ke_daftar(): void
    {
        $data = $this->setupData();

        $this->browse(function (Browser $browser) use ($data) {
            $browser->loginAs($data['admin'])
                ->visit(route('admin.disposisi.create', ['laporan_id' => $data['laporan']->id]))
                ->assertSee('Terbitkan Surat Perintah Tugas (SPT)')
                ->click('@btn-batal-disposisi')
                ->assertPathIs('/admin/disposisi')
                ->assertSee('Daftar Laporan Siap Disposisi');
        });

        $this->assertEquals(0, DisposisiPenugasan::count());
    }
}

