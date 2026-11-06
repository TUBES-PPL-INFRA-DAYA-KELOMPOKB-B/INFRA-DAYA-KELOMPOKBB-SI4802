<?php

namespace Tests\Feature;

use App\Models\LaporanKerusakan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HardeningAndFinalisasiTest extends TestCase
{
    use RefreshDatabase;

    public function test_halaman_error_403_ditampilkan_saat_akses_dilarang(): void
    {
        $warga = User::factory()->create([
            'role' => 'warga',
            'is_active' => true,
        ]);

        $response = $this->actingAs($warga)->get('/admin/users');

        $response->assertStatus(403);
        $response->assertSee('Wewenang Tidak Mencukupi');
        $response->assertSee('403 • Akses Terbatas');
        $response->assertSee('Buka Dashboard');
    }

    public function test_halaman_error_404_ditampilkan_saat_halaman_tidak_ada(): void
    {
        $response = $this->get('/halaman-fiktif-tidak-pernah-ada-999');

        $response->assertStatus(404);
        $response->assertSee('Halaman Tidak Ditemukan');
        $response->assertSee('404 • Tidak Ditemukan');
        $response->assertSee('Lacak Nomor Tiket');
    }

    public function test_akun_nonaktif_dikeluarkan_otomatis(): void
    {
        $userNonaktif = User::factory()->create([
            'role' => 'warga',
            'is_active' => false,
        ]);

        $response = $this->actingAs($userNonaktif)->get('/warga/laporan-saya');

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors(['email']);
        $this->assertGuest();
    }

    public function test_query_performa_dengan_composite_indexes_berjalan_akurat(): void
    {
        LaporanKerusakan::factory()->count(5)->create([
            'status' => 'selesai',
        ]);

        $hasil = LaporanKerusakan::query()
            ->where('status', 'selesai')
            ->whereDate('created_at', now()->toDateString())
            ->get();

        $this->assertNotEmpty($hasil);
    }
}
