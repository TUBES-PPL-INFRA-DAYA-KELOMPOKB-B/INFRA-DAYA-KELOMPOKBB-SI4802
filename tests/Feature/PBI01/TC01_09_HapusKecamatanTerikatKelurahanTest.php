<?php

namespace Tests\Feature\PBI01;

use App\Models\Kecamatan;
use App\Models\Kelurahan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * TC01-09 [Exception]
 * Prasyarat: ADM1 login; KEC1 terikat data kelurahan
 * Langkah Pengujian: Pilih Hapus pada KEC1 -> konfirmasi
 * Input: KEC1 (memiliki kelurahan)
 * Expected Output: Ditolak; muncul pesan kecamatan tidak dapat dihapus karena memiliki kelurahan; data tetap
 */
class TC01_09_HapusKecamatanTerikatKelurahanTest extends TestCase
{
    use RefreshDatabase;

    public function test_tc01_09_hapus_kecamatan_yang_terikat_kelurahan_ditolak(): void
    {
        $adm1 = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $kec1 = Kecamatan::create([
            'kode_kecamatan' => '32.73.08',
            'nama_kecamatan' => 'Coblong',
        ]);

        Kelurahan::create([
            'kecamatan_id' => $kec1->id,
            'kode_kelurahan' => '32.73.08.1001',
            'nama_kelurahan' => 'Dago',
        ]);

        $response = $this->actingAs($adm1)->delete(route('admin.kecamatan.destroy', $kec1->id));

        $response->assertRedirect(route('admin.kecamatan.index'));
        $response->assertSessionHas('error');

        $this->assertDatabaseHas('kecamatan', [
            'id' => $kec1->id,
            'kode_kecamatan' => '32.73.08',
            'nama_kecamatan' => 'Coblong',
        ]);
    }
}
