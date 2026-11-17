<?php

namespace Tests\Feature\PBI01;

use App\Models\Kecamatan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * TC01-12 [Exception]
 * Prasyarat: Pengguna belum login
 * Langkah Pengujian: Akses daftar dan endpoint mutasi kecamatan
 * Input: Request tanpa sesi
 * Expected Output: Diminta autentikasi / dialihkan ke halaman login; data tetap
 */
class TC01_12_PenggunaBelumLoginMutasi302Test extends TestCase
{
    use RefreshDatabase;

    public function test_tc01_12_pengguna_belum_login_dialihkan_ke_halaman_login_302(): void
    {
        $kec = Kecamatan::create([
            'kode_kecamatan' => '32.73.08',
            'nama_kecamatan' => 'Coblong',
        ]);

        // GET index tanpa sesi
        $this->get(route('admin.kecamatan.index'))->assertRedirect(route('login'));

        // POST store tanpa sesi
        $this->post(route('admin.kecamatan.store'), [
            'kode_kecamatan' => '32.73.01',
            'nama_kecamatan' => 'Andir',
        ])->assertRedirect(route('login'));

        // PUT update tanpa sesi
        $this->put(route('admin.kecamatan.update', $kec->id), [
            'kode_kecamatan' => '32.73.08',
            'nama_kecamatan' => 'Coblong Baru',
        ])->assertRedirect(route('login'));

        // DELETE destroy tanpa sesi
        $this->delete(route('admin.kecamatan.destroy', $kec->id))->assertRedirect(route('login'));

        // Data tetap utuh
        $this->assertDatabaseHas('kecamatan', [
            'id' => $kec->id,
            'nama_kecamatan' => 'Coblong',
        ]);
    }
}
