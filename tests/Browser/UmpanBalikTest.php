<?php

namespace Tests\Browser;

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
 * Fase 9: Pengujian E2E Evaluasi & Umpan Balik Kepuasan Warga (Bintang 1-5 & Ulasan Pasca Perbaikan).
 */
class UmpanBalikTest extends DuskTestCase
{
    use DatabaseMigrations;

    private function createData(): array
    {
        $warga = User::factory()->create([
            'name' => 'Dewi Sartika',
            'email' => 'dewi.'.uniqid().'@warga.bandung.go.id',
            'role' => 'warga',
            'is_active' => true,
        ]);

        $kecamatan = Kecamatan::create([
            'kode_kecamatan' => 'BDG-006',
            'nama_kecamatan' => 'Coblong',
        ]);

        $kelurahan = Kelurahan::create([
            'kecamatan_id' => $kecamatan->id,
            'kode_kelurahan' => 'BDG-006-01',
            'nama_kelurahan' => 'Sekeloa',
        ]);

        $kategori = KategoriInfrastruktur::create([
            'nama_kategori' => 'Penerangan Jalan Umum',
            'deskripsi' => 'Lampu jalan mati / rusak',
            'bobot_prioritas' => 3,
        ]);

        $laporan = LaporanKerusakan::create([
            'nomor_tiket' => 'INFRA-20261014-7777',
            'warga_id' => $warga->id,
            'kategori_id' => $kategori->id,
            'kelurahan_id' => $kelurahan->id,
            'judul' => 'Lampu PJU Padam di Jalan Dipati Ukur',
            'deskripsi_kondisi' => 'Lampu jalan padam total 3 tiang berturut-turut.',
            'latitude' => -6.8920000,
            'longitude' => 107.6180000,
            'status' => 'selesai',
            'prioritas' => 'tinggi',
            'foto_bukti_sebelum' => 'laporan/sebelum/lampu.jpg',
            'foto_bukti_sesudah' => 'laporan/sesudah/lampu_nyala.jpg',
            'catatan_perbaikan' => 'Bohlam LED 120W telah diganti dan kabel fitting dirapikan.',
            'selesai_at' => now(),
        ]);

        return compact('warga', 'laporan');
    }

    /**
     * Warga dapat memberikan evaluasi rating bintang dan ulasan komentar pada laporan yang telah selesai.
     */
    public function test_warga_dapat_memberikan_rating_bintang_dan_ulasan_pada_laporan_selesai(): void
    {
        $data = $this->createData();

        $this->browse(function (Browser $browser) use ($data) {
            $browser->loginAs($data['warga'])
                ->visit(route('warga.laporan.show', $data['laporan']->id))
                ->assertSee('Evaluasi Kepuasan Perbaikan')
                ->click('@rating-star-5')
                ->select('@select-kecepatan', '5')
                ->select('@select-kualitas', '5')
                ->type('@input-komentar', 'Luar biasa! Penerangan kembali terang benderang malam ini. Terima kasih tim DSDABM Kota Bandung.')
                ->press('@btn-submit-umpan-balik')
                ->waitForText('Ulasan & Evaluasi Anda')
                ->assertSee('Ulasan & Evaluasi Anda')
                ->assertSee('5 dari 5 Bintang')
                ->assertSee('Luar biasa! Penerangan kembali terang benderang malam ini')
                ->assertMissing('@btn-submit-umpan-balik');
        });
    }

    /**
     * Laporan yang sudah memiliki rating tidak menampilkan form pengisian ulang.
     */
    public function test_form_rating_tidak_muncul_jika_sudah_pernah_dinilai(): void
    {
        $data = $this->createData();

        UmpanBalik::create([
            'laporan_id' => $data['laporan']->id,
            'warga_id' => $data['warga']->id,
            'rating' => 4,
            'komentar' => 'Perbaikan sudah rapi dan bagus.',
            'kecepatan_penanganan' => 4,
            'kualitas_perbaikan' => 4,
        ]);

        $this->browse(function (Browser $browser) use ($data) {
            $browser->loginAs($data['warga'])
                ->visit(route('warga.laporan.show', $data['laporan']->id))
                ->assertSee('Ulasan & Evaluasi Anda')
                ->assertSee('4 dari 5 Bintang')
                ->assertSee('Perbaikan sudah rapi dan bagus.')
                ->assertMissing('@btn-submit-umpan-balik');
        });
    }
}
