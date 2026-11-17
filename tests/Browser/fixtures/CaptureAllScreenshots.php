<?php

namespace Tests\Browser;

use App\Models\LaporanKerusakan;
use App\Models\User;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class CaptureAllScreenshotsTest extends DuskTestCase
{
    public function test_capture_all_views(): void
    {
        $warga = User::where('role', 'warga')->first();
        $admin = User::where('role', 'admin')->first();
        $petugas = User::where('role', 'petugas')->first();
        $pimpinan = User::where('role', 'pimpinan')->first();

        $lapMenunggu = LaporanKerusakan::where('status', 'menunggu_verifikasi')->first();
        $lapDiproses = LaporanKerusakan::where('status', 'diproses')->first();
        $lapSelesai = LaporanKerusakan::where('status', 'selesai')->first();

        $this->browse(function (Browser $browser) use ($warga, $admin, $petugas, $pimpinan, $lapMenunggu, $lapDiproses, $lapSelesai) {
            // 1. Beranda Publik
            $browser->resize(1366, 768)
                ->visit('/')
                ->pause(800)
                ->screenshot('01_beranda_publik');

            // 2. Peta Sebaran Publik
            $browser->visit('/peta-publik')
                ->waitFor('#leafletPublicMap', 5)
                ->pause(1500)
                ->screenshot('02_peta_sebaran_publik');

            // 3. Lacak Tiket Publik
            $tiket = $lapSelesai ? $lapSelesai->nomor_tiket : 'INFRA-20261114-0004';
            $browser->visit('/lacak-tiket/'.$tiket)
                ->pause(1000)
                ->screenshot('03_lacak_tiket_publik');

            // 4. Halaman Login
            $browser->visit('/login')
                ->pause(500)
                ->screenshot('04_login_page');

            // 5. Halaman Register Warga
            $browser->visit('/register')
                ->pause(500)
                ->screenshot('05_register_warga');

            // 6. Warga: Buat Laporan (Tampilan Mobile 390x844)
            $browser->resize(390, 844)
                ->loginAs($warga)
                ->visit(route('warga.laporan.create'))
                ->waitFor('#leafletMap', 5)
                ->pause(1000)
                ->screenshot('06_warga_buat_laporan_mobile');

            // 7. Warga: Pinpoint Peta Leaflet (Scroll ke peta)
            $browser->script("window.scrollTo(0, 600);");
            $browser->pause(600)
                ->screenshot('07_warga_pinpoint_leaflet_mobile');

            // 8. Warga: Riwayat Laporan Saya (Tampilan Mobile)
            $browser->visit(route('warga.laporan.saya'))
                ->pause(800)
                ->screenshot('08_warga_riwayat_laporan_mobile');

            // 9. Warga: Detail Laporan & Modal Pembatalan (Desktop)
            $browser->resize(1366, 768);
            if ($lapMenunggu) {
                $browser->visit(route('warga.laporan.show', $lapMenunggu->id))
                    ->pause(1000)
                    ->screenshot('09_warga_detail_laporan_desktop');

                $browser->visit(route('warga.laporan.edit', $lapMenunggu->id))
                    ->pause(1000)
                    ->screenshot('10_warga_ubah_laporan_desktop');
            }

            // 11. Admin: Antrean Verifikasi Laporan
            $browser->loginAs($admin)
                ->visit(route('admin.verifikasi.index'))
                ->pause(1000)
                ->screenshot('11_admin_antrean_verifikasi');

            // 12. Admin: Detail Verifikasi Laporan
            if ($lapMenunggu) {
                $browser->visit(route('admin.verifikasi.show', $lapMenunggu->id))
                    ->pause(1000)
                    ->screenshot('12_admin_detail_verifikasi');
            }

            // 13. Admin: Disposisi Penugasan Lapangan
            $browser->visit(route('admin.disposisi.index'))
                ->pause(1000)
                ->screenshot('13_admin_disposisi_penugasan');

            // 14. Admin: Master Data Wilayah (Kecamatan & Kelurahan)
            $browser->visit(route('admin.kecamatan.index'))
                ->pause(1000)
                ->screenshot('14_admin_master_wilayah');

            // 15. Admin: Master Data Kategori
            $browser->visit(route('admin.kategori.index'))
                ->pause(1000)
                ->screenshot('15_admin_master_kategori');

            // 16. Admin: Manajemen Pengguna
            $browser->visit(route('admin.users.index'))
                ->pause(1000)
                ->screenshot('16_admin_manajemen_user');

            // 17. Admin: Rekapitulasi Data & Ekspor Excel/PDF
            $browser->visit(route('admin.rekap.index'))
                ->pause(1000)
                ->screenshot('17_admin_rekap_dan_ekspor');

            // 18. Petugas: Lembar Kerja Lapangan (Tampilan Mobile 390x844)
            $browser->resize(390, 844)
                ->loginAs($petugas)
                ->visit(route('petugas.tugas.index'))
                ->pause(1000)
                ->screenshot('18_petugas_tugas_lapangan_mobile');

            // 19. Pimpinan / Admin: Dashboard Eksekutif & Monitoring SLA (Desktop)
            $browser->resize(1366, 768)
                ->loginAs($pimpinan)
                ->visit(route('dashboard'))
                ->pause(1500)
                ->screenshot('19_pimpinan_dashboard_eksekutif_sla');
        });
    }
}

