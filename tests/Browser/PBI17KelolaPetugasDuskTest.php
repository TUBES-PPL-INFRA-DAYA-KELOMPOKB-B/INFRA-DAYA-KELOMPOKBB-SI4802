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

class PBI17KelolaPetugasDuskTest extends DuskTestCase
{
    use DatabaseMigrations;

    private function setupData(): array
    {
        $admin = User::factory()->create([
            'name' => 'Admin DSDABM',
            'email' => 'admin.dusk.' . uniqid() . '@bandung.go.id',
            'role' => 'admin',
            'is_active' => true,
        ]);

        $petugas = User::factory()->create([
            'name' => 'Agus Karyawan',
            'email' => 'agus.' . uniqid() . '@bandung.go.id',
            'phone' => '081234567890',
            'role' => 'petugas',
            'is_active' => true,
        ]);

        $kecamatan = Kecamatan::create([
            'kode_kecamatan' => 'BDG-088',
            'nama_kecamatan' => 'Sumur Bandung',
        ]);

        $kelurahan = Kelurahan::create([
            'kecamatan_id' => $kecamatan->id,
            'kode_kelurahan' => 'BDG-088-01',
            'nama_kelurahan' => 'Braga',
        ]);

        $kategori = KategoriInfrastruktur::create([
            'nama_kategori' => 'Drainase Rusak',
            'deskripsi' => 'Kerusakan saluran air',
            'bobot_prioritas' => 2,
        ]);

        $laporan = LaporanKerusakan::create([
            'nomor_tiket' => 'LAP-DUSK-1701',
            'warga_id' => $admin->id,
            'kelurahan_id' => $kelurahan->id,
            'kategori_id' => $kategori->id,
            'judul' => 'Saluran Air Tersumbat di Braga',
            'deskripsi_kondisi' => 'Saluran air meluap ke badan jalan.',
            'latitude' => -6.9175,
            'longitude' => 107.6091,
            'foto_bukti_sebelum' => 'reports/sample_before.jpg',
            'status' => 'diproses',
            'prioritas' => 'sedang',
        ]);

        return compact('admin', 'petugas', 'laporan');
    }

    /**
     * E2E: Admin melihat daftar petugas, menambah petugas baru, dan melakukan pencarian.
     */
    public function test_e2e_admin_melihat_daftar_menambah_dan_mencari_petugas(): void
    {
        $data = $this->setupData();
        $newPetugasEmail = 'dani.teknisi.' . uniqid() . '@bandung.go.id';

        $this->browse(function (Browser $browser) use ($data, $newPetugasEmail) {
            $browser->loginAs($data['admin'])
                // 1. Buka halaman kelola petugas
                ->visit(route('admin.petugas.index'))
                ->assertSee('Pengelolaan Petugas Lapangan')
                ->assertSee($data['petugas']->name)
                ->assertPresent('@btn-create-petugas')

                // 2. Klik Tambah Petugas Baru
                ->click('@btn-create-petugas')
                ->assertPathIs('/admin/petugas/create')
                ->assertSee('Tambah Petugas Lapangan')
                ->assertSee('Role: Petugas')

                // 3. Isi formulir pembuatan akun petugas
                ->type('@input-name', 'Dani Teknisi Baru')
                ->type('@input-email', $newPetugasEmail)
                ->type('@input-phone', '+6281234567899')
                ->type('@input-password', 'PetugasPassword123!')
                ->type('@input-password-confirmation', 'PetugasPassword123!')
                ->press('@btn-submit')

                // 4. Kembali ke daftar petugas dengan pesan sukses
                ->waitForLocation('/admin/petugas')
                ->assertSee('Dani Teknisi Baru')
                ->assertSee('+6281234567899')

                // 5. Uji pencarian berdasarkan nama petugas
                ->type('@input-search', 'Dani Teknisi')
                ->press('@btn-filter')
                ->assertSee('Dani Teknisi Baru')
                ->assertDontSee($data['petugas']->name);
        });
    }

    /**
     * E2E: Admin melihat detail profil petugas dan memperbarui identitasnya.
     */
    public function test_e2e_admin_melihat_detail_dan_edit_identitas_petugas(): void
    {
        $data = $this->setupData();

        $this->browse(function (Browser $browser) use ($data) {
            $browser->loginAs($data['admin'])
                // 1. Kunjungi halaman detail petugas
                ->visit(route('admin.petugas.show', $data['petugas']->id))
                ->assertSee('Detail Profil Petugas Lapangan')
                ->assertSee($data['petugas']->name)
                ->assertSee($data['petugas']->email)
                ->assertPresent('@btn-edit')

                // 2. Klik Edit Identitas
                ->click('@btn-edit')
                ->assertPathIs('/admin/petugas/' . $data['petugas']->id . '/edit')
                ->assertSee('Edit Identitas Petugas')

                // 3. Ubah nama dan nomor telepon
                ->clear('@input-name')
                ->type('@input-name', 'Agus Karyawan Senior')
                ->clear('@input-phone')
                ->type('@input-phone', '085566778899')
                ->press('@btn-submit')

                // 4. Diarahkan kembali ke halaman detail dengan data baru
                ->waitForLocation('/admin/petugas/' . $data['petugas']->id)
                ->assertSee('Agus Karyawan Senior')
                ->assertSee('085566778899');
        });
    }

    /**
     * E2E: Nonaktivasi petugas berhasil jika tidak memiliki tugas belum selesai, lalu diaktifkan kembali.
     */
    public function test_e2e_nonaktivasi_dan_reaktivasi_petugas_lapangan(): void
    {
        $data = $this->setupData();

        $this->browse(function (Browser $browser) use ($data) {
            $browser->loginAs($data['admin'])
                ->visit(route('admin.petugas.index'))
                ->assertSeeIn('@status-badge-' . $data['petugas']->id, 'Aktif')

                // 1. Klik tombol toggle status untuk membuka modal konfirmasi
                ->click('@btn-toggle-' . $data['petugas']->id)
                ->waitFor('@btn-confirm-toggle-' . $data['petugas']->id)
                ->click('@btn-confirm-toggle-' . $data['petugas']->id)

                // 2. Berhasil dinonaktifkan
                ->waitForLocation('/admin/petugas')
                ->assertSeeIn('@status-badge-' . $data['petugas']->id, 'Nonaktif')

                // 3. Aktifkan kembali
                ->click('@btn-toggle-' . $data['petugas']->id)
                ->waitFor('@btn-confirm-toggle-' . $data['petugas']->id)
                ->click('@btn-confirm-toggle-' . $data['petugas']->id)

                // 4. Status kembali menjadi Aktif
                ->waitForLocation('/admin/petugas')
                ->assertSeeIn('@status-badge-' . $data['petugas']->id, 'Aktif');
        });
    }

    /**
     * E2E: Nonaktivasi ditolak jika petugas memiliki penugasan yang belum selesai (AC17-14).
     */
    public function test_e2e_nonaktivasi_ditolak_jika_memiliki_tugas_belum_selesai(): void
    {
        $data = $this->setupData();

        // Buat penugasan aktif untuk petugas
        DisposisiPenugasan::create([
            'nomor_surat_tugas' => 'SPT-DUSK-0099',
            'laporan_id' => $data['laporan']->id,
            'admin_id' => $data['admin']->id,
            'petugas_id' => $data['petugas']->id,
            'tanggal_disposisi' => now(),
            'catatan_instruksi' => 'Instruksi perbaikan aktif.',
            'status_penugasan' => 'ditugaskan',
        ]);

        $this->browse(function (Browser $browser) use ($data) {
            $browser->loginAs($data['admin'])
                ->visit(route('admin.petugas.index'))

                // Coba nonaktifkan
                ->click('@btn-toggle-' . $data['petugas']->id)
                ->waitFor('@btn-confirm-toggle-' . $data['petugas']->id)
                ->click('@btn-confirm-toggle-' . $data['petugas']->id)

                // Sistem menolak dan menampilkan informasi tugas penghambat
                ->waitFor('@alert-blocking-tasks')
                ->assertSee('Penonaktifan Ditolak')
                ->assertSee($data['laporan']->nomor_tiket)
                ->assertSeeIn('@status-badge-' . $data['petugas']->id, 'Aktif');
        });
    }
}

