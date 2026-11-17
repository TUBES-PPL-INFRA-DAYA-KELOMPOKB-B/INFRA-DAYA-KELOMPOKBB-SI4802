<?php

namespace Tests\Feature\PBI01;

use App\Models\Kecamatan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * TC01-02 [Normal]
 * Prasyarat: ADM1 login
 * Langkah Pengujian: Cari berdasarkan nama dan kode secara terpisah
 * Input: "Coblong"; "32.73.15"
 * Expected Output: Hanya akun/baris kecamatan yang cocok ditampilkan
 */
class TC01_02_CariBerdasarkanNamaDanKodeTest extends TestCase
{
    use RefreshDatabase;

    public function test_tc01_02_cari_berdasarkan_nama_dan_kode_secara_terpisah(): void
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

        // 1. Pencarian berdasarkan Nama: "Coblong"
        $resName = $this->actingAs($adm1)->get(route('admin.kecamatan.index', ['search' => 'Coblong']));
        $resName->assertOk();
        $resName->assertSee('Coblong');
        $resName->assertDontSee('Sukasari');

        // 2. Pencarian berdasarkan Kode: "32.73.15"
        $resCode = $this->actingAs($adm1)->get(route('admin.kecamatan.index', ['search' => '32.73.15']));
        $resCode->assertOk();
        $resCode->assertSee('Sukasari');
        $resCode->assertDontSee('Coblong');
    }
}
