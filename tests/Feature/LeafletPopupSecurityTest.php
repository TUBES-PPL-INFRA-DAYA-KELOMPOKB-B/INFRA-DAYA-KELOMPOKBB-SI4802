<?php

namespace Tests\Feature;

use App\Models\KategoriInfrastruktur;
use App\Models\Kecamatan;
use App\Models\Kelurahan;
use App\Models\LaporanKerusakan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeafletPopupSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_report_detail_renders_safely_with_special_characters_in_title(): void
    {
        $kecamatan = Kecamatan::create(['kode_kecamatan' => 'BDG-01', 'nama_kecamatan' => 'Coblong']);
        $kelurahan = Kelurahan::create(['kecamatan_id' => $kecamatan->id, 'kode_kelurahan' => 'BDG-01-01', 'nama_kelurahan' => 'Dago']);
        $kategori = KategoriInfrastruktur::create(['nama_kategori' => 'Jalan', 'deskripsi' => 'Tes', 'bobot_prioritas' => 2]);
        $warga = User::factory()->create(['role' => 'warga']);
        $admin = User::factory()->create(['role' => 'admin']);

        $laporan = LaporanKerusakan::create([
            'nomor_tiket' => 'INFRA-20261014-0001',
            'warga_id' => $warga->id,
            'kelurahan_id' => $kelurahan->id,
            'kategori_id' => $kategori->id,
            'judul' => 'Jalan Rusak "Amblas" Parah & Berbahaya',
            'deskripsi_kondisi' => 'Kondisi parah',
            'latitude' => -6.90000000,
            'longitude' => 107.61000000,
            'foto_bukti_sebelum' => 'laporan/sebelum/test.jpg',
            'status' => 'menunggu_verifikasi',
            'prioritas' => 'sedang',
        ]);

        // Warga show view
        $wargaResponse = $this->actingAs($warga)->get(route('warga.laporan.show', $laporan->id));
        $wargaResponse->assertStatus(200);
        $wargaResponse->assertSee('popupContent');

        // Admin verifikasi show view
        $adminResponse = $this->actingAs($admin)->get(route('admin.verifikasi.show', $laporan->id));
        $adminResponse->assertStatus(200);
        $adminResponse->assertSee('popupContent');
    }
}
