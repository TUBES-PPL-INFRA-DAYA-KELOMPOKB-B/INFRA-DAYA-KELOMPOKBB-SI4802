<?php

namespace Tests\Browser;

use App\Models\KategoriInfrastruktur;
use App\Models\Kelurahan;
use App\Models\LaporanKerusakan;
use App\Models\User;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/**
 * TC01-10: A1 login; U2 memiliki transaksi dan gagal dihapus.
 * Langkah: Pilih Nonaktifkan pada U2 -> konfirmasi -> periksa laporan LP-001
 * Expected: Status U2 berubah menjadi Nonaktif; record U2 dan laporan LP-001 tetap tersimpan utuh.
 */
class TC01_10_Test extends DuskTestCase
{
    public function test_tc01_10(): void
    {
        $admin = User::factory()->create([
            'email' => 'admin.tc0110.'.uniqid().'@dsdabm.bandung.go.id',
            'role' => 'admin',
            'is_active' => true,
        ]);

        $u2 = User::factory()->create([
            'name' => 'Siti Transaksi Tetap Utuh',
            'email' => 'siti.nonaktif.'.uniqid().'@gmail.com',
            'role' => 'warga',
            'is_active' => true,
        ]);

        $kelurahan = Kelurahan::first() ?? Kelurahan::factory()->create();
        $kategori = KategoriInfrastruktur::first() ?? KategoriInfrastruktur::factory()->create();

        $laporan = LaporanKerusakan::create([
            'nomor_tiket' => 'LP-001-'.rand(100, 999),
            'warga_id' => $u2->id,
            'kelurahan_id' => $kelurahan->id,
            'kategori_id' => $kategori->id,
            'judul' => 'Lampu Jalan Rusak LP-001',
            'deskripsi_kondisi' => 'Penerangan mati sejak kemarin',
            'latitude' => -6.914744,
            'longitude' => 107.609810,
            'foto_bukti_sebelum' => 'laporan/dummy.jpg',
            'status' => 'menunggu_verifikasi',
        ]);

        $this->browse(function (Browser $browser) use ($admin, $u2) {
            $browser->loginAs($admin)
                ->visit(route('admin.users.index', ['search' => $u2->name]))
                ->waitFor("@toggle-active-btn-{$u2->id}")
                ->click("@toggle-active-btn-{$u2->id}")
                ->waitForLocation('/admin/users')
                ->assertSee('dinonaktifkan');
        });

        // Verifikasi database: U2 berstatus nonaktif (is_active = 0)
        $this->assertDatabaseHas('users', [
            'id' => $u2->id,
            'is_active' => 0,
        ]);

        // Verifikasi laporan LP-001 tetap tersimpan utuh di database
        $this->assertDatabaseHas('laporan_kerusakan', [
            'id' => $laporan->id,
            'warga_id' => $u2->id,
        ]);
    }
}
