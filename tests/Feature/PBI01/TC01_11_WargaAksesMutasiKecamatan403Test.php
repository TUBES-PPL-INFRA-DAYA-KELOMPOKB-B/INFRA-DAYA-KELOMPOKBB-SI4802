<?php

namespace Tests\Feature\PBI01;

use App\Models\Kecamatan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * TC01-11 [Exception]
 * Prasyarat: W1 telah login (Warga)
 * Langkah Pengujian: Akses URL daftar dan kirim endpoint mutasi langsung
 * Input: Request GET/POST/PUT/DELETE ke /admin/kecamatan
 * Expected Output: Akses ditolak (403 Forbidden); data target tetap
 */
class TC01_11_WargaAksesMutasiKecamatan403Test extends TestCase
{
    use RefreshDatabase;

    public function test_tc01_11_warga_akses_daftar_dan_mutasi_ditolak_403(): void
    {
        $w1 = User::factory()->create([
            'role' => 'warga',
            'is_active' => true,
        ]);

        $kec = Kecamatan::create([
            'kode_kecamatan' => '32.73.08',
            'nama_kecamatan' => 'Coblong',
        ]);

        // 1. GET index
        $this->actingAs($w1)->get(route('admin.kecamatan.index'))->assertStatus(403);

        // 2. POST store
        $this->actingAs($w1)->post(route('admin.kecamatan.store'), [
            'kode_kecamatan' => '32.73.77',
            'nama_kecamatan' => 'Kecamatan Ilegal',
        ])->assertStatus(403);

        // 3. PUT update
        $this->actingAs($w1)->put(route('admin.kecamatan.update', $kec->id), [
            'kode_kecamatan' => '32.73.08',
            'nama_kecamatan' => 'Coblong Diubah Warga',
        ])->assertStatus(403);

        // 4. DELETE destroy
        $this->actingAs($w1)->delete(route('admin.kecamatan.destroy', $kec->id))->assertStatus(403);

        // Pastikan data target tetap
        $this->assertDatabaseHas('kecamatan', [
            'id' => $kec->id,
            'nama_kecamatan' => 'Coblong',
        ]);
    }
}
