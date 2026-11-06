<?php

namespace Tests\Browser;

use App\Models\KategoriInfrastruktur;
use App\Models\Kecamatan;
use App\Models\Kelurahan;
use App\Models\LaporanKerusakan;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/**
 * Fase 4: Pengujian E2E Pengajuan Laporan oleh Warga (Leaflet, Form, Policy).
 */
class LaporanWargaTest extends DuskTestCase
{
    use DatabaseMigrations;

    private function createSetupData(): array
    {
        $kecamatan = Kecamatan::factory()->create([
            'nama_kecamatan' => 'Coblong',
            'kode_kecamatan' => 'KEC-COBLONG',
        ]);

        $kelurahan = Kelurahan::factory()->create([
            'kecamatan_id' => $kecamatan->id,
            'nama_kelurahan' => 'Dago',
            'kode_kelurahan' => 'KEL-DAGO',
        ]);

        $kategori = KategoriInfrastruktur::factory()->create([
            'nama_kategori' => 'Jalan Raya / Trotoar',
            'bobot_prioritas' => 3,
        ]);

        $warga = User::factory()->create([
            'name' => 'Warga Pelapor',
            'email' => 'warga.lapor.'.uniqid().'@test.com',
            'role' => 'warga',
        ]);

        return compact('kecamatan', 'kelurahan', 'kategori', 'warga');
    }

    /**
     * Warga dapat membuka form pengajuan laporan dan melihat elemen peta.
     */
    public function test_warga_can_view_lapor_form_with_map(): void
    {
        $data = $this->createSetupData();

        $this->browse(function (Browser $browser) use ($data) {
            $browser->loginAs($data['warga'])
                ->visit(route('warga.laporan.create'))
                ->assertSee('Lapor Kerusakan Infrastruktur')
                ->assertPresent('#leafletMap')
                ->assertPresent('select[name="kategori_id"]');
        });
    }

    /**
     * Warga dapat mengajukan laporan baru dan menerima nomor tiket.
     */
    public function test_warga_can_submit_report(): void
    {
        $data = $this->createSetupData();

        $this->browse(function (Browser $browser) use ($data) {
            $browser->loginAs($data['warga'])
                ->visit(route('warga.laporan.create'))
                ->type('judul', 'Jalan Berlubang di Dekat Simpang Dago')
                ->select('kategori_id', (string) $data['kategori']->id)
                ->type('deskripsi_kondisi', 'Terdapat lubang berdiameter 60cm yang membahayakan pengendara motor.')
                ->select('#kecamatan_select', (string) $data['kecamatan']->id)
                ->waitFor("#kelurahan_id option[value='{$data['kelurahan']->id}']", 5)
                ->select('#kelurahan_id', (string) $data['kelurahan']->id)
                ->attach('foto_bukti_sebelum', __DIR__.'/fixtures/sample_kerusakan.jpg')
                ->click('@submit-btn')
                ->waitForText('berhasil dikirim')
                ->assertSee('berhasil dikirim')
                ->assertSee('INFRA-');
        });
    }

    /**
     * PBI-8: Warga dapat menggeser pin marker Leaflet, verifikasi koordinat terisi dinamis,
     * serta menekan tombol konfirmasi titik lokasi (SKF-03, NFR-01, NFR-03).
     */
    public function test_warga_can_interact_with_leaflet_map_and_confirm_pinpoint(): void
    {
        $data = $this->createSetupData();

        $this->browse(function (Browser $browser) use ($data) {
            $browser->loginAs($data['warga'])
                ->visit(route('warga.laporan.create'))
                ->waitFor('#leafletMap', 2) // NFR-01: waktu render elemen maksimal 2 detik
                ->assertPresent('#leafletMap')
                ->assertPresent('#btnUseMyLocation')
                ->assertInputValue('#latitude', '-6.91746400')
                ->assertInputValue('#longitude', '107.61912300');

            // Simulasi pergeseran pin marker secara interaktif pada Leaflet.js
            $browser->script([
                'marker.setLatLng([-6.92500000, 107.63000000]);',
                "marker.fire('dragend');",
            ]);

            // Verifikasi input koordinat terkunci terisi dinamis saat pin bergeser
            $browser->pause(300)
                ->assertInputValue('#latitude', '-6.92500000')
                ->assertInputValue('#longitude', '107.63000000')
                ->assertSeeIn('@coord-display', '-6.92500000')
                ->assertPresent('@konfirmasi-titik-btn')
                ->click('@konfirmasi-titik-btn')
                ->waitFor('@konfirmasi-badge', 2)
                ->assertSeeIn('@konfirmasi-badge', 'Titik koordinat berhasil dikonfirmasi')
                ->type('@patokan-lokasi', 'Dekat Bundaran Cibiru')
                ->type('judul', 'Jalan Rusak Pasca Pergeseran Pin')
                ->select('kategori_id', (string) $data['kategori']->id)
                ->type('deskripsi_kondisi', 'Titik lubang ditentukan dengan pin marker presisi.')
                ->select('#kecamatan_select', (string) $data['kecamatan']->id)
                ->waitFor("#kelurahan_id option[value='{$data['kelurahan']->id}']", 5)
                ->select('#kelurahan_id', (string) $data['kelurahan']->id)
                ->attach('foto_bukti_sebelum', __DIR__.'/fixtures/sample_kerusakan.jpg')
                ->click('@submit-btn')
                ->waitForText('berhasil dikirim')
                ->assertSee('berhasil dikirim');

            $this->assertDatabaseHas('laporan_kerusakan', [
                'latitude' => -6.92500000,
                'longitude' => 107.63000000,
            ]);
        });
    }

    /**
     * Validasi: judul laporan wajib diisi.
     */
    public function test_lapor_creation_requires_mandatory_fields(): void
    {
        $data = $this->createSetupData();

        $this->browse(function (Browser $browser) use ($data) {
            $browser->loginAs($data['warga'])
                ->visit(route('warga.laporan.create'))
                ->click('@submit-btn')
                ->assertPathIs('/warga/lapor');
        });
    }

    /**
     * Warga dapat melihat daftar laporan miliknya di halaman "Laporan Saya".
     */
    public function test_warga_can_view_own_reports(): void
    {
        $data = $this->createSetupData();

        $laporan = LaporanKerusakan::factory()->create([
            'nomor_tiket' => 'INFRA-20261013-0001',
            'warga_id' => $data['warga']->id,
            'kelurahan_id' => $data['kelurahan']->id,
            'kategori_id' => $data['kategori']->id,
            'judul' => 'Laporan Kerusakan Trotoar Dago',
            'deskripsi_kondisi' => 'Ubin trotoar pecah-pecah',
            'latitude' => -6.90000000,
            'longitude' => 107.61000000,
            'foto_bukti_sebelum' => 'laporan/sebelum/sample.jpg',
            'status' => 'menunggu_verifikasi',
        ]);

        $this->browse(function (Browser $browser) use ($data, $laporan) {
            $browser->loginAs($data['warga'])
                ->visit(route('warga.laporan.saya'))
                ->assertSee('Riwayat Laporan Saya')
                ->assertSee($laporan->nomor_tiket)
                ->assertSee($laporan->judul);
        });
    }

    /**
     * Proteksi IDOR: Warga A dilarang mengubah laporan milik Warga B.
     */
    public function test_warga_cannot_edit_another_warga_report(): void
    {
        $data = $this->createSetupData();

        $wargaB = User::factory()->create([
            'email' => 'warga.b.'.uniqid().'@test.com',
            'role' => 'warga',
        ]);

        $laporanWargaA = LaporanKerusakan::factory()->create([
            'nomor_tiket' => 'INFRA-20261013-0099',
            'warga_id' => $data['warga']->id,
            'kelurahan_id' => $data['kelurahan']->id,
            'kategori_id' => $data['kategori']->id,
            'foto_bukti_sebelum' => 'laporan/sebelum/sample.jpg',
            'status' => 'menunggu_verifikasi',
        ]);

        $this->browse(function (Browser $browser) use ($wargaB, $laporanWargaA) {
            $browser->loginAs($wargaB)
                ->visit(route('warga.laporan.edit', $laporanWargaA->id))
                ->assertSee('403');
        });
    }
}
