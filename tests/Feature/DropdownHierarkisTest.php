<?php

namespace Tests\Feature;

use App\Models\Kecamatan;
use App\Models\Kelurahan;
use App\Models\User;
use App\Services\WilayahService;
use Database\Seeders\WilayahSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * PBI-3: Automated Testing Dropdown Hierarkis (Kecamatan -> Kelurahan).
 * Memverifikasi FR-03-01 sampai FR-03-05 dan Kriteria Penerimaan Bagian 12 PRD.
 */
class DropdownHierarkisTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Kriteria Penerimaan: Seeder menghasilkan 30 kecamatan dan 151 kelurahan.
     */
    public function test_seeder_populates_30_kecamatan_and_151_kelurahan(): void
    {
        $this->seed(WilayahSeeder::class);

        $this->assertEquals(30, Kecamatan::count(), 'Jumlah kecamatan hasil seeder harus tepat 30.');
        $this->assertEquals(151, Kelurahan::count(), 'Jumlah kelurahan hasil seeder harus tepat 151.');
    }

    /**
     * FR-03-01: Dropdown kecamatan menampilkan 30 kecamatan dari tabel kecamatan, diurutkan berdasarkan nama.
     */
    public function test_dropdown_kecamatan_lists_all_kecamatan_sorted_alphabetically(): void
    {
        $this->seed(WilayahSeeder::class);

        $wilayahService = app(WilayahService::class);
        $kecamatanList = $wilayahService->getAllKecamatan();

        $this->assertCount(30, $kecamatanList);

        $names = $kecamatanList->pluck('nama_kecamatan')->toArray();
        $sortedNames = $names;
        sort($sortedNames, SORT_STRING | SORT_FLAG_CASE);

        $this->assertEquals($sortedNames, $names, 'Daftar kecamatan harus terurut abjad (A-Z).');
    }

    /**
     * FR-03-02 & FR-03-05: Dropdown kelurahan menampilkan hanya kelurahan dari kecamatan terpilih via JSON tanpa reload.
     */
    public function test_api_kelurahan_by_kecamatan_returns_only_kelurahan_belonging_to_selected_kecamatan(): void
    {
        $this->seed(WilayahSeeder::class);

        $kecamatan = Kecamatan::where('nama_kecamatan', 'Antapani')->firstOrFail();

        $response = $this->getJson(route('api.wilayah.kelurahan', $kecamatan->id));

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'kecamatan_id' => $kecamatan->id,
            ]);

        $data = $response->json('data');
        $this->assertNotEmpty($data, 'Kecamatan Antapani harus memiliki kelurahan.');

        // Pastikan seluruh kelurahan yang dikembalikan berelasi ke kecamatan_id yang diminta
        foreach ($data as $item) {
            $this->assertEquals($kecamatan->id, $item['kecamatan_id']);
            $this->assertNotEmpty($item['kode_kelurahan']);
            $this->assertNotEmpty($item['nama_kelurahan']);
        }

        // Kecamatan Antapani memiliki 4 kelurahan resmi (Antapani Kidul, Antapani Kulon, Antapani Tengah, Antapani Wetan)
        $this->assertCount(4, $data);
    }

    /**
     * FR-03-02: Daftar kelurahan diurutkan berdasarkan nama_kelurahan.
     */
    public function test_api_kelurahan_by_kecamatan_returns_sorted_kelurahan(): void
    {
        $this->seed(WilayahSeeder::class);

        $kecamatan = Kecamatan::where('nama_kecamatan', 'Coblong')->firstOrFail();

        $response = $this->getJson(route('api.wilayah.kelurahan', $kecamatan->id));

        $response->assertOk();
        $names = collect($response->json('data'))->pluck('nama_kelurahan')->toArray();

        $sortedNames = $names;
        sort($sortedNames, SORT_STRING | SORT_FLAG_CASE);

        $this->assertEquals($sortedNames, $names, 'Daftar kelurahan pada dropdown harus terurut alfabetis.');
    }

    /**
     * FR-03-03: Endpoint mengembalikan array kosong jika kecamatan tidak memiliki kelurahan.
     */
    public function test_api_kelurahan_returns_empty_when_no_kelurahan_linked(): void
    {
        $kecamatan = Kecamatan::create([
            'kode_kecamatan' => '327399',
            'nama_kecamatan' => 'Kecamatan Uji Kosong',
        ]);

        $response = $this->getJson(route('api.wilayah.kelurahan', $kecamatan->id));

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'kecamatan_id' => $kecamatan->id,
                'data' => [],
            ]);
    }

    /**
     * Warga dapat membuka halaman UI Pilih Wilayah Kerusakan (PBI-03).
     */
    public function test_warga_can_access_pilih_wilayah_page(): void
    {
        $this->seed(WilayahSeeder::class);

        $warga = User::factory()->create([
            'role' => 'warga',
            'is_active' => true,
        ]);

        $response = $this->actingAs($warga)->get(route('warga.laporan.pilih-wilayah'));

        $response->assertOk()
            ->assertSee('SPRINT 1 / PBI 03 / WARGA')
            ->assertSee('Pilih wilayah kerusakan')
            ->assertSee('01 / Wilayah administratif')
            ->assertSee('02 / Lokasi pada peta')
            ->assertSee('Lanjut ke detail laporan')
            ->assertSee('Ganti kecamatan akan mengosongkan pilihan kelurahan dan memusatkan ulang peta.')
            ->assertSee('Peta skematis • Kota Bandung • bukan koordinat operasional');
    }

    /**
     * Tamu/pengguna belum login dialihkan ke halaman login saat mencoba mengakses halaman pilih wilayah.
     */
    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get(route('warga.laporan.pilih-wilayah'));

        $response->assertRedirect(route('login'));
    }

    /**
     * FR-03-04: Komponen dropdown wilayah reusable dapat dirender tanpa error.
     */
    public function test_wilayah_dropdown_component_can_render(): void
    {
        $this->seed(WilayahSeeder::class);

        $kecamatanList = Kecamatan::orderBy('nama_kecamatan')->get();

        $view = $this->blade(
            '<x-wilayah-dropdown :kecamatanList="$kecamatanList" />',
            ['kecamatanList' => $kecamatanList]
        );

        $view->assertSee('kecamatan_select')
            ->assertSee('kelurahan_id')
            ->assertSee('Pilih Kecamatan Kota Bandung');
    }
}

