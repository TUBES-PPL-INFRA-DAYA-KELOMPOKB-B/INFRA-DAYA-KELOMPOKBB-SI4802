<?php

namespace Tests\Browser;

use App\Models\KategoriInfrastruktur;
use App\Models\Kelurahan;
use App\Models\LaporanKerusakan;
use App\Models\User;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/**
 * TC01-08: Pilih Hapus pada U2 (U2 sudah memiliki transaksi laporan)
 * Expected: Penghapusan ditolak; sistem menampilkan pesan bahwa U2 memiliki riwayat transaksi dan hanya dapat dinonaktifkan
 */
class TC01_08_Test extends DuskTestCase
{
    public function test_tc01_08(): void
    {
        $admin = User::factory()->create([
            'email' => 'admin.tc0108.'.uniqid().'@dsdabm.bandung.go.id',
            'role' => 'admin',
            'is_active' => true,
        ]);

        $u2 = User::factory()->create([
            'name' => 'Siti Punya Laporan',
            'email' => 'siti.transaksi.'.uniqid().'@gmail.com',
            'role' => 'warga',
        ]);

        $kelurahan = Kelurahan::first() ?? Kelurahan::factory()->create();
        $kategori = KategoriInfrastruktur::first() ?? KategoriInfrastruktur::factory()->create();

        $laporan = LaporanKerusakan::create([
            'nomor_tiket' => 'LP-TC0108-'.rand(100, 999),
            'warga_id' => $u2->id,
            'kelurahan_id' => $kelurahan->id,
            'kategori_id' => $kategori->id,
            'judul' => 'Jalan Berlubang Dekat Rumah',
            'deskripsi_kondisi' => 'Kondisi cukup membahayakan',
            'latitude' => -6.914744,
            'longitude' => 107.609810,
            'foto_bukti_sebelum' => 'laporan/dummy.jpg',
            'status' => 'menunggu_verifikasi',
        ]);

        $this->browse(function (Browser $browser) use ($admin, $u2) {
            $browser->loginAs($admin)
                ->visit(route('admin.users.index', ['search' => $u2->name]))
                ->waitFor("@delete-btn-{$u2->id}")
                ->assertSee($u2->name)
                ->click("@delete-btn-{$u2->id}")
                ->waitFor('#deleteModal')
                ->click('@confirm-delete-btn')
                ->waitForLocation('/admin/users')
                ->assertSee('riwayat transaksi')
                ->assertSee('dinonaktifkan');
        });

        $this->assertDatabaseHas('users', ['id' => $u2->id]);
        $this->assertDatabaseHas('laporan_kerusakan', ['id' => $laporan->id]);
    }
}
