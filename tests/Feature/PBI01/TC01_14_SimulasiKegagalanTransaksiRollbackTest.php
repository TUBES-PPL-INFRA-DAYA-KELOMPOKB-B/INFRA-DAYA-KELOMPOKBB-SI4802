<?php

namespace Tests\Feature\PBI01;

use App\Models\User;
use App\Services\WilayahService;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery\MockInterface;
use Tests\TestCase;

/**
 * TC01-14 [Exception]
 * Prasyarat: ADM1 login
 * Langkah Pengujian: Simulasikan kegagalan transaksi basis data saat mutasi
 * Input: Fault penyimpanan basis data
 * Expected Output: Rollback; tidak ada perubahan parsial; pesan gagal ditampilkan
 */
class TC01_14_SimulasiKegagalanTransaksiRollbackTest extends TestCase
{
    use RefreshDatabase;

    public function test_tc01_14_simulasi_kegagalan_transaksi_memicu_rollback_dan_pesan_gagal(): void
    {
        $adm1 = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        // Simulasikan fault penyimpanan basis data pada layer service
        $this->mock(WilayahService::class, function (MockInterface $mock) {
            $mock->shouldReceive('createKecamatan')
                ->once()
                ->andThrow(new Exception('Simulasi kegagalan transaksi basis data internal.'));
        });

        $payload = [
            'kode_kecamatan' => '32.73.55',
            'nama_kecamatan' => 'Kecamatan Gagal',
        ];

        $response = $this->actingAs($adm1)->post(route('admin.kecamatan.store'), $payload);

        // Harus dialihkan kembali dengan session pesan error
        $response->assertRedirect(route('admin.kecamatan.index'));
        $response->assertSessionHas('error');

        // Pastikan tidak ada data parsial tersimpan di database
        $this->assertDatabaseMissing('kecamatan', [
            'kode_kecamatan' => '32.73.55',
        ]);
    }
}
