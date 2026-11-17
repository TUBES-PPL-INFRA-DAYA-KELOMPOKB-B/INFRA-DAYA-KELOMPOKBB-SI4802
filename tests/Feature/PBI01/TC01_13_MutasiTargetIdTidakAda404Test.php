<?php

namespace Tests\Feature\PBI01;

use App\Models\Kecamatan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * TC01-13 [Exception]
 * Prasyarat: ADM1 login
 * Langkah Pengujian: Kirim mutasi (Update/Delete) terhadap KEC_X (ID tidak ada)
 * Input: Target ID tidak ada (misal 999999)
 * Expected Output: Ditolak (404 Not Found); record lain tetap utuh
 */
class TC01_13_MutasiTargetIdTidakAda404Test extends TestCase
{
    use RefreshDatabase;

    public function test_tc01_13_mutasi_id_tidak_ada_menghasilkan_404(): void
    {
        $adm1 = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $kec = Kecamatan::create([
            'kode_kecamatan' => '32.73.08',
            'nama_kecamatan' => 'Coblong',
        ]);

        $targetIdTidakAda = 999999;

        // PUT terhadap ID 999999
        $resPut = $this->actingAs($adm1)->put(route('admin.kecamatan.update', $targetIdTidakAda), [
            'kode_kecamatan' => '32.73.88',
            'nama_kecamatan' => 'Kecamatan Fiktif',
        ]);
        $resPut->assertStatus(404);

        // DELETE terhadap ID 999999
        $resDelete = $this->actingAs($adm1)->delete(route('admin.kecamatan.destroy', $targetIdTidakAda));
        $resDelete->assertStatus(404);

        // Record lain tetap utuh
        $this->assertDatabaseHas('kecamatan', [
            'id' => $kec->id,
            'nama_kecamatan' => 'Coblong',
        ]);
    }
}
