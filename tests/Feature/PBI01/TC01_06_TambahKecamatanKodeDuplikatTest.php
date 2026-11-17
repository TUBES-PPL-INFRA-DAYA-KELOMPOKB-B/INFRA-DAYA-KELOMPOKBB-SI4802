<?php

namespace Tests\Feature\PBI01;

use App\Models\Kecamatan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * TC01-06 [Exception]
 * Prasyarat: ADM1 login; KEC1 tersedia
 * Langkah Pengujian: Tambah kecamatan dengan kode yang sama dengan KEC1
 * Input: Kode: "32.73.08"; Nama: "Coblong Baru"
 * Expected Output: Ditolak; sistem menampilkan pesan kode kecamatan sudah terdaftar
 */
class TC01_06_TambahKecamatanKodeDuplikatTest extends TestCase
{
    use RefreshDatabase;

    public function test_tc01_06_tambah_kecamatan_dengan_kode_duplikat_ditolak(): void
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
            'kode_kecamatan' => $kec1->kode_kecamatan, // "32.73.08"
            'nama_kecamatan' => 'Coblong Baru',
        ];

        $response = $this->actingAs($adm1)->post(route('admin.kecamatan.store'), $payload);

        $response->assertSessionHasErrors(['kode_kecamatan']);
        $this->assertDatabaseMissing('kecamatan', [
            'nama_kecamatan' => 'Coblong Baru',
        ]);
    }
}
