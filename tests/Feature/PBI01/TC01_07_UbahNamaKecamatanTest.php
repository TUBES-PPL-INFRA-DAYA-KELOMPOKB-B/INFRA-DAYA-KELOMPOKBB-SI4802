<?php

namespace Tests\Feature\PBI01;

use App\Models\Kecamatan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * TC01-07 [Normal]
 * Prasyarat: ADM1 login; KEC1 tersedia
 * Langkah Pengujian: Kelola KEC1 -> ubah nama -> simpan -> muat ulang
 * Input: KEC1; Nama baru: "Coblong Raya"
 * Expected Output: Nama kecamatan terbarui di UI dan database; kode tetap
 */
class TC01_07_UbahNamaKecamatanTest extends TestCase
{
    use RefreshDatabase;

    public function test_tc01_07_ubah_nama_kecamatan_berhasil_dan_kode_tetap(): void
    {
        $adm1 = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $kec1 = Kecamatan::create([
            'kode_kecamatan' => '32.73.08',
            'nama_kecamatan' => 'Coblong',
        ]);

        $payload = [
            'kode_kecamatan' => '32.73.08',
            'nama_kecamatan' => 'Coblong Raya',
        ];

        $response = $this->actingAs($adm1)->put(route('admin.kecamatan.update', $kec1->id), $payload);

        $response->assertRedirect(route('admin.kecamatan.index'));
        $response->assertSessionHas('success', 'Data kecamatan berhasil diperbarui.');

        $this->assertDatabaseHas('kecamatan', [
            'id' => $kec1->id,
            'kode_kecamatan' => '32.73.08',
            'nama_kecamatan' => 'Coblong Raya',
        ]);

        $indexRes = $this->actingAs($adm1)->get(route('admin.kecamatan.index'));
        $indexRes->assertSee('Coblong Raya');
        $indexRes->assertSee('32.73.08');
    }
}
