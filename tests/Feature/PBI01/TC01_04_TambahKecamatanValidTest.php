<?php

namespace Tests\Feature\PBI01;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * TC01-04 [Normal]
 * Prasyarat: ADM1 login
 * Langkah Pengujian: Buka form Tambah -> isi data valid -> simpan
 * Input: Kode: "32.73.30"; Nama: "Rancasari"
 * Expected Output: Data tersimpan di UI dan database; muncul notifikasi berhasil
 */
class TC01_04_TambahKecamatanValidTest extends TestCase
{
    use RefreshDatabase;

    public function test_tc01_04_tambah_kecamatan_data_valid_tersimpan_di_ui_dan_database(): void
    {
        $adm1 = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $payload = [
            'kode_kecamatan' => '32.73.30',
            'nama_kecamatan' => 'Rancasari',
        ];

        $response = $this->actingAs($adm1)->post(route('admin.kecamatan.store'), $payload);

        $response->assertRedirect(route('admin.kecamatan.index'));
        $response->assertSessionHas('success', 'Data kecamatan baru berhasil ditambahkan.');

        $this->assertDatabaseHas('kecamatan', [
            'kode_kecamatan' => '32.73.30',
            'nama_kecamatan' => 'Rancasari',
        ]);

        $indexRes = $this->actingAs($adm1)->get(route('admin.kecamatan.index'));
        $indexRes->assertSee('Rancasari');
        $indexRes->assertSee('32.73.30');
    }
}
