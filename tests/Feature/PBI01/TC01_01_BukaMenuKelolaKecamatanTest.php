<?php

namespace Tests\Feature\PBI01;

use App\Models\Kecamatan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * TC01-01 [Normal]
 * Prasyarat: ADM1 login; KEC1/KEC2 tersedia
 * Langkah Pengujian: Buka menu Kelola Kecamatan
 * Input: Sesi ADM1
 * Expected Output: Seluruh data kecamatan tampil; antarmuka kelola termuat
 */
class TC01_01_BukaMenuKelolaKecamatanTest extends TestCase
{
    use RefreshDatabase;

    public function test_tc01_01_seluruh_data_kecamatan_tampil_dan_antarmuka_kelola_termuat(): void
    {
        $adm1 = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $kec1 = Kecamatan::create([
            'kode_kecamatan' => '32.73.08',
            'nama_kecamatan' => 'Coblong',
        ]);

        $kec2 = Kecamatan::create([
            'kode_kecamatan' => '32.73.15',
            'nama_kecamatan' => 'Sukasari',
        ]);

        $response = $this->actingAs($adm1)->get(route('admin.kecamatan.index'));

        $response->assertOk();
        $response->assertViewIs('admin.wilayah.kecamatan.index');
        $response->assertSee('Kelola data kecamatan');
        $response->assertSee($kec1->nama_kecamatan);
        $response->assertSee($kec1->kode_kecamatan);
        $response->assertSee($kec2->nama_kecamatan);
        $response->assertSee($kec2->kode_kecamatan);
    }
}
