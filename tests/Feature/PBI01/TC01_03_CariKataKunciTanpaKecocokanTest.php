<?php

namespace Tests\Feature\PBI01;

use App\Models\Kecamatan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * TC01-03 [Exception]
 * Prasyarat: ADM1 login
 * Langkah Pengujian: Cari kata kunci tanpa kecocokan
 * Input: "KecamatanXYZ"
 * Expected Output: Pesan data tidak ditemukan; tidak terjadi error sistem
 */
class TC01_03_CariKataKunciTanpaKecocokanTest extends TestCase
{
    use RefreshDatabase;

    public function test_tc01_03_cari_kata_kunci_tanpa_kecocokan_menampilkan_pesan_tanpa_error(): void
    {
        $adm1 = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        Kecamatan::create([
            'kode_kecamatan' => '32.73.08',
            'nama_kecamatan' => 'Coblong',
        ]);

        $response = $this->actingAs($adm1)->get(route('admin.kecamatan.index', ['search' => 'KecamatanXYZ']));

        $response->assertOk();
        $response->assertDontSee('Coblong');
        $response->assertSee('Tidak ada data kecamatan ditemukan');
    }
}
