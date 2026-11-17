<?php

namespace Tests\Feature\PBI01;

use App\Models\Kecamatan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * TC01-08 [Normal]
 * Prasyarat: ADM1 login
 * Langkah Pengujian: Ubah nilai pada modal/form -> pilih Batal
 * Input: Perubahan sementara; Batal
 * Expected Output: Modal tertutup; nilai data asli tidak berubah
 */
class TC01_08_BatalUbahKecamatanModalTest extends TestCase
{
    use RefreshDatabase;

    public function test_tc01_08_batal_ubah_tidak_mengubah_nilai_data_asli(): void
    {
        $adm1 = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $kec = Kecamatan::create([
            'kode_kecamatan' => '32.73.08',
            'nama_kecamatan' => 'Coblong Asli',
        ]);

        // Masuk form ubah
        $editRes = $this->actingAs($adm1)->get(route('admin.kecamatan.index', ['edit_id' => $kec->id]));
        $editRes->assertOk();
        $editRes->assertSee('Batal');

        // Batal: kembali ke daftar tanpa submit perubahan
        $cancelRes = $this->actingAs($adm1)->get(route('admin.kecamatan.index'));
        $cancelRes->assertOk();

        $this->assertDatabaseHas('kecamatan', [
            'id' => $kec->id,
            'nama_kecamatan' => 'Coblong Asli',
            'kode_kecamatan' => '32.73.08',
        ]);
    }
}
