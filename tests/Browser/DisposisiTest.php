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
 * Fase 6: Pengujian E2E Disposisi Penugasan & Surat Perintah Tugas (SPT).
 */
class DisposisiTest extends DuskTestCase
{
    use DatabaseMigrations;

    private function createData(): array
    {
        $admin = User::factory()->create([
            'name' => 'Admin DSDABM',
            'email' => 'admin.disp.'.uniqid().'@dsdabm.bandung.go.id',
            'role' => 'admin',
            'is_active' => true,
        ]);

        $petugas = User::factory()->create([
            'name' => 'Joko Petugas Lapangan',
            'email' => 'petugas.'.uniqid().'@dsdabm.bandung.go.id',
            'phone' => '081234567890',
            'role' => 'petugas',
            'is_active' => true,
        ]);

        $warga = User::factory()->create([
            'name' => 'Ahmad Warga',
            'email' => 'warga.'.uniqid().'@test.com',
            'role' => 'warga',
            'is_active' => true,
        ]);

        $kecamatan = Kecamatan::create([
            'kode_kecamatan' => 'BDG-002',
            'nama_kecamatan' => 'Sumur Bandung',
        ]);

        $kelurahan = Kelurahan::create([
            'kecamatan_id' => $kecamatan->id,
            'kode_kelurahan' => 'BDG-002-01',
            'nama_kelurahan' => 'Braga',
        ]);

        $kategori = KategoriInfrastruktur::create([
            'nama_kategori' => 'Drainase Perkotaan',
            'deskripsi' => 'Saluran air tersumbat atau jebol',
            'bobot_prioritas' => 2,
        ]);

        $laporan = LaporanKerusakan::create([
            'nomor_tiket' => 'INFRA-20261014-0099',
            'warga_id' => $warga->id,
            'kelurahan_id' => $kelurahan->id,
            'kategori_id' => $kategori->id,
            'judul' => 'Gorong-gorong Amblas di Jalan Asia Afrika',
            'deskripsi_kondisi' => 'Saluran drainase ambles selebar 1 meter memicu genangan saat hujan deras.',
            'latitude' => -6.9218,
            'longitude' => 107.6074,
            'foto_bukti_sebelum' => 'laporan/sebelum/sample.jpg',
            'status' => 'diproses',
            'prioritas' => 'tinggi',
        ]);

        return compact('admin', 'petugas', 'warga', 'laporan');
    }

    /**
     * Admin dapat melihat daftar antrean disposisi penugasan.
     */
    public function test_admin_can_view_disposisi_index(): void
    {
        $data = $this->createData();

        DisposisiPenugasan::factory()->create([
            'nomor_surat_tugas' => 'SPT-20261014-8888',
            'laporan_id' => $data['laporan']->id,
            'admin_id' => $data['admin']->id,
            'petugas_id' => $data['petugas']->id,
        ]);

        $this->browse(function (Browser $browser) use ($data) {
            $browser->loginAs($data['admin'])
                ->visit(route('admin.disposisi.index'))
                ->assertSee('Disposisi Penugasan Lapangan')
                ->assertSee('SPT-20261014-8888')
                ->assertSee($data['petugas']->name)
                ->assertPresent('table');
        });
    }

    /**
     * Admin dapat menerbitkan surat perintah tugas (SPT) untuk laporan yang disetujui.
     */
    public function test_admin_can_create_disposisi_for_approved_report(): void
    {
        $data = $this->createData();

        $this->browse(function (Browser $browser) use ($data) {
            $browser->loginAs($data['admin'])
                ->visit(route('admin.disposisi.create', ['laporan_id' => $data['laporan']->id]))
                ->assertSee('Terbitkan Surat Perintah Tugas (SPT)')
                ->assertSee('Ringkasan Laporan Terpilih')
                ->select('petugas_id', (string) $data['petugas']->id)
                ->type('catatan_instruksi', 'Segera bersihkan sedimen saluran dan lakukan pengecoran dinding penahan drainase.')
                ->press('@btn-submit-disposisi')
                ->waitForText('Surat Perintah Tugas')
                ->assertSee('berhasil diterbitkan')
                ->assertSee($data['petugas']->name)
                ->assertPresent('@btn-unduh-pdf');
        });
    }

    /**
     * Admin dapat melihat detail SPT dan elemen peta lokasi pengerjaan.
     */
    public function test_admin_can_view_disposisi_detail_and_map(): void
    {
        $data = $this->createData();

        $disposisi = DisposisiPenugasan::factory()->create([
            'nomor_surat_tugas' => 'SPT-20261014-7777',
            'laporan_id' => $data['laporan']->id,
            'admin_id' => $data['admin']->id,
            'petugas_id' => $data['petugas']->id,
            'catatan_instruksi' => 'Instruksi pengerjaan drainase jalan Asia Afrika.',
        ]);

        $this->browse(function (Browser $browser) use ($data, $disposisi) {
            $browser->loginAs($data['admin'])
                ->visit(route('admin.disposisi.show', $disposisi->id))
                ->assertSee('SPT-20261014-7777')
                ->assertSee('Instruksi pengerjaan drainase jalan Asia Afrika.')
                ->assertSee($data['petugas']->name)
                ->assertPresent('#disposisiMap');
        });
    }

    /**
     * Non-admin dilarang mengakses halaman disposisi.
     */
    public function test_non_admin_cannot_access_disposisi(): void
    {
        $data = $this->createData();

        $this->browse(function (Browser $browser) use ($data) {
            $browser->loginAs($data['warga'])
                ->visit(route('admin.disposisi.index'))
                ->assertSee('403');
        });
    }
}
