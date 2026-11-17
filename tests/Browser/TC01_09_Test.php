<?php

namespace Tests\Browser;

use App\Models\DisposisiPenugasan;
use App\Models\KategoriInfrastruktur;
use App\Models\Kelurahan;
use App\Models\LaporanKerusakan;
use App\Models\User;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/**
 * TC01-09: A1 login; U3 sudah memiliki transaksi disposisi. Pilih Hapus pada U3.
 * Expected: Penghapusan ditolak; record U3 dan disposisi DP-001 tetap utuh.
 */
class TC01_09_Test extends DuskTestCase
{
    public function test_tc01_09(): void
    {
        $admin = User::factory()->create([
            'email' => 'admin.tc0109.'.uniqid().'@dsdabm.bandung.go.id',
            'role' => 'admin',
            'is_active' => true,
        ]);

        $u3 = User::factory()->create([
            'name' => 'Budi Petugas Lapangan',
            'email' => 'budi.disposisi.'.uniqid().'@dsdabm.bandung.go.id',
            'role' => 'petugas',
            'is_active' => true,
        ]);

        $kelurahan = Kelurahan::first() ?? Kelurahan::factory()->create();
        $kategori = KategoriInfrastruktur::first() ?? KategoriInfrastruktur::factory()->create();

        $laporan = LaporanKerusakan::create([
            'nomor_tiket' => 'LP-TC0109-'.rand(100, 999),
            'warga_id' => $admin->id,
            'kelurahan_id' => $kelurahan->id,
            'kategori_id' => $kategori->id,
            'judul' => 'Saluran Air Tersumbat',
            'deskripsi_kondisi' => 'Genangan air meluap',
            'latitude' => -6.914744,
            'longitude' => 107.609810,
            'foto_bukti_sebelum' => 'laporan/dummy.jpg',
            'status' => 'diproses',
        ]);

        $disposisi = DisposisiPenugasan::create([
            'nomor_surat_tugas' => 'DP-001-'.rand(100, 999),
            'laporan_id' => $laporan->id,
            'admin_id' => $admin->id,
            'petugas_id' => $u3->id,
            'tanggal_disposisi' => now(),
            'tenggat_waktu' => now()->addDays(3),
            'catatan_instruksi' => 'Segera tangani ke lokasi DP-001',
            'status_penugasan' => 'ditugaskan',
        ]);

        $this->browse(function (Browser $browser) use ($admin, $u3) {
            $browser->loginAs($admin)
                ->visit(route('admin.users.index', ['search' => $u3->name]))
                ->waitFor("@delete-btn-{$u3->id}")
                ->assertSee($u3->name)
                ->click("@delete-btn-{$u3->id}")
                ->waitFor('#deleteModal')
                ->click('@confirm-delete-btn')
                ->waitForLocation('/admin/users')
                ->assertSee('riwayat transaksi')
                ->assertSee('dinonaktifkan');
        });

        $this->assertDatabaseHas('users', ['id' => $u3->id]);
        $this->assertDatabaseHas('disposisi_penugasan', ['id' => $disposisi->id]);
    }
}
