<?php

namespace Tests\Feature\PBI01;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * TC01-05 [Exception]
 * Prasyarat: ADM1 login
 * Langkah Pengujian: Kirim penambahan dengan nama kecamatan kosong dan hanya spasi
 * Input: Kode: "32.73.31"; Nama: "" dan " "
 * Expected Output: Ditolak; muncul pesan nama kecamatan wajib diisi
 */
class TC01_05_TambahKecamatanNamaKosongSpasiTest extends TestCase
{
    use RefreshDatabase;

    public function test_tc01_05_tambah_kecamatan_nama_kosong_dan_spasi_ditolak(): void
    {
        $adm1 = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        // 1. Nama kosong ("")
        $resEmpty = $this->actingAs($adm1)->post(route('admin.kecamatan.store'), [
            'kode_kecamatan' => '32.73.31',
            'nama_kecamatan' => '',
        ]);
        $resEmpty->assertSessionHasErrors(['nama_kecamatan']);

        // 2. Nama hanya spasi ("   ")
        $resSpace = $this->actingAs($adm1)->post(route('admin.kecamatan.store'), [
            'kode_kecamatan' => '32.73.31',
            'nama_kecamatan' => '   ',
        ]);
        $resSpace->assertSessionHasErrors(['nama_kecamatan']);

        $this->assertDatabaseMissing('kecamatan', [
            'kode_kecamatan' => '32.73.31',
        ]);
    }
}
