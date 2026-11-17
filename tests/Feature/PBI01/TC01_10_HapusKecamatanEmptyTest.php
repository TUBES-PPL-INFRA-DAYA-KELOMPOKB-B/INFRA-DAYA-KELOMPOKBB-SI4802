<?php

namespace Tests\Feature\PBI01;

use App\Models\Kecamatan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * TC01-10 [Normal]
 * Prasyarat: ADM1 login; KEC_EMPTY tersedia
 * Langkah Pengujian: Pilih Hapus pada KEC_EMPTY -> konfirmasi
 * Input: KEC_EMPTY; konfirmasi Hapus
 * Expected Output: Data terhapus dari tabel database; baris hilang dari tampilan
 */
class TC01_10_HapusKecamatanEmptyTest extends TestCase
{
    use RefreshDatabase;

    public function test_tc01_10_hapus_kecamatan_empty_berhasil(): void
    {
        $adm1 = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $kecEmpty = Kecamatan::create([
            'kode_kecamatan' => '32.73.99',
            'nama_kecamatan' => 'Kecamatan Kosong',
        ]);

        $response = $this->actingAs($adm1)->delete(route('admin.kecamatan.destroy', $kecEmpty->id));

        $response->assertRedirect(route('admin.kecamatan.index'));
        $response->assertSessionHas('success', 'Data kecamatan berhasil dihapus.');

        $this->assertDatabaseMissing('kecamatan', [
            'id' => $kecEmpty->id,
        ]);

        $indexRes = $this->actingAs($adm1)->get(route('admin.kecamatan.index'));
        $indexRes->assertDontSee('Kecamatan Kosong');
    }
}
