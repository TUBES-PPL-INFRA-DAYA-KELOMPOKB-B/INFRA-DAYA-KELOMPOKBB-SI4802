<?php

namespace Tests\Feature;

use App\Models\KategoriInfrastruktur;
use App\Models\User;
use App\Services\KategoriService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * PBI-12 (Epic IFD-5): Pengujian Otomatis Fitur Filter & Pencarian Master Kategori.
 *
 * Menguji seluruh Kriteria Penerimaan dan Skenario Pengujian T-12.1 sampai T-12.4 & KF-12.1 s/d KF-12.7:
 * - T-12.1: Pencarian berdasarkan nama/deskripsi kategori -> hanya data yang cocok tampil.
 * - T-12.2: Filter berdasarkan tingkat keparahan default -> hanya kategori dengan tingkat keparahan terpilih yang tampil.
 * - T-12.3: Kombinasi filter dan pencarian -> hanya kategori yang memenuhi kedua kondisi yang tampil.
 * - T-12.4: Reset filter mengembalikan seluruh daftar kategori; jika tidak ada hasil yang cocok, menampilkan pesan "Data kategori tidak ditemukan."
 * - KF-12.4: Parameter filter dipertahankan dalam query string paginasi (withQueryString).
 * - KF-12.7: Komponen Blade <x-filter-kategori> dapat di-render dan dipakai ulang.
 */
class KategoriPbi12FilterTest extends TestCase
{
    use RefreshDatabase;

    private function createAdmin(): User
    {
        return User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);
    }

    /**
     * T-12.1 (KF-12.1) & AC 1: Pencarian berdasarkan kata kunci pada nama kategori.
     * Given terdapat empat kategori, when Admin mengetik "drainase", then hanya kategori drainase yang tampil.
     */
    public function test_t12_1_pencarian_kategori_berdasarkan_nama(): void
    {
        $admin = $this->createAdmin();

        KategoriInfrastruktur::factory()->create([
            'nama_kategori' => 'Drainase Perkotaan',
            'deskripsi' => 'Saluran air limpasan hujan perkotaan',
            'tingkat_keparahan_default' => 'tinggi',
            'bobot_prioritas' => 3,
        ]);
        KategoriInfrastruktur::factory()->create([
            'nama_kategori' => 'Jalan Raya Utama',
            'deskripsi' => 'Perkerasan aspal jalan protokol',
            'tingkat_keparahan_default' => 'sedang',
            'bobot_prioritas' => 2,
        ]);
        KategoriInfrastruktur::factory()->create([
            'nama_kategori' => 'Lampu Penerangan Jalan',
            'deskripsi' => 'Penerangan umum kota malam hari',
            'tingkat_keparahan_default' => 'rendah',
            'bobot_prioritas' => 1,
        ]);
        KategoriInfrastruktur::factory()->create([
            'nama_kategori' => 'Jembatan Penyeberangan',
            'deskripsi' => 'Jembatan fasilitas pejalan kaki',
            'tingkat_keparahan_default' => 'kritis',
            'bobot_prioritas' => 4,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.kategori.index', ['search' => 'drainase']));

        $response->assertOk();
        $response->assertSee('Drainase Perkotaan');
        $response->assertDontSee('Jalan Raya Utama');
        $response->assertDontSee('Lampu Penerangan Jalan');
        $response->assertDontSee('Jembatan Penyeberangan');
    }

    /**
     * T-12.1 (KF-12.1): Pencarian berdasarkan kata kunci pada deskripsi kategori.
     */
    public function test_t12_1_pencarian_kategori_berdasarkan_deskripsi(): void
    {
        $admin = $this->createAdmin();

        KategoriInfrastruktur::factory()->create([
            'nama_kategori' => 'Saluran Limbah',
            'deskripsi' => 'Gorong-gorong pembuangan air limbah tertutup',
            'tingkat_keparahan_default' => 'sedang',
            'bobot_prioritas' => 2,
        ]);
        KategoriInfrastruktur::factory()->create([
            'nama_kategori' => 'Rambu Lalu Lintas',
            'deskripsi' => 'Petunjuk arah dan peringatan bahaya',
            'tingkat_keparahan_default' => 'rendah',
            'bobot_prioritas' => 1,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.kategori.index', ['search' => 'gorong-gorong']));

        $response->assertOk();
        $response->assertSee('Saluran Limbah');
        $response->assertDontSee('Rambu Lalu Lintas');
    }

    /**
     * T-12.2 (KF-12.2) & AC 2: Filter berdasarkan tingkat keparahan "Kritis".
     * Given Admin memilih filter "Kritis", when diterapkan, then hanya kategori berkeparahan "Kritis" yang tampil.
     */
    public function test_t12_2_filter_kategori_berdasarkan_tingkat_keparahan_kritis(): void
    {
        $admin = $this->createAdmin();

        KategoriInfrastruktur::factory()->create([
            'nama_kategori' => 'Tanggul Jebol',
            'tingkat_keparahan_default' => 'kritis',
            'bobot_prioritas' => 4,
        ]);
        KategoriInfrastruktur::factory()->create([
            'nama_kategori' => 'Jalan Longsor',
            'tingkat_keparahan_default' => 'kritis',
            'bobot_prioritas' => 4,
        ]);
        KategoriInfrastruktur::factory()->create([
            'nama_kategori' => 'Trotoar Rusak',
            'tingkat_keparahan_default' => 'rendah',
            'bobot_prioritas' => 1,
        ]);
        KategoriInfrastruktur::factory()->create([
            'nama_kategori' => 'Pohon Tumbang',
            'tingkat_keparahan_default' => 'tinggi',
            'bobot_prioritas' => 3,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.kategori.index', ['keparahan' => 'kritis']));

        $response->assertOk();
        $response->assertSee('Tanggul Jebol');
        $response->assertSee('Jalan Longsor');
        $response->assertDontSee('Trotoar Rusak');
        $response->assertDontSee('Pohon Tumbang');
    }

    /**
     * T-12.2 (KF-12.2): Filter dapat menyaring setiap tingkatan keparahan (Rendah, Sedang, Tinggi).
     */
    public function test_t12_2_filter_kategori_berdasarkan_tingkat_keparahan_lainnya(): void
    {
        $admin = $this->createAdmin();

        KategoriInfrastruktur::factory()->create([
            'nama_kategori' => 'Kategori Rendah Test',
            'tingkat_keparahan_default' => 'rendah',
            'bobot_prioritas' => 1,
        ]);
        KategoriInfrastruktur::factory()->create([
            'nama_kategori' => 'Kategori Sedang Test',
            'tingkat_keparahan_default' => 'sedang',
            'bobot_prioritas' => 2,
        ]);
        KategoriInfrastruktur::factory()->create([
            'nama_kategori' => 'Kategori Tinggi Test',
            'tingkat_keparahan_default' => 'tinggi',
            'bobot_prioritas' => 3,
        ]);

        // Filter Rendah
        $resRendah = $this->actingAs($admin)->get(route('admin.kategori.index', ['keparahan' => 'rendah']));
        $resRendah->assertSee('Kategori Rendah Test');
        $resRendah->assertDontSee('Kategori Sedang Test');
        $resRendah->assertDontSee('Kategori Tinggi Test');

        // Filter Sedang
        $resSedang = $this->actingAs($admin)->get(route('admin.kategori.index', ['keparahan' => 'sedang']));
        $resSedang->assertSee('Kategori Sedang Test');
        $resSedang->assertDontSee('Kategori Rendah Test');
        $resSedang->assertDontSee('Kategori Tinggi Test');

        // Filter Tinggi
        $resTinggi = $this->actingAs($admin)->get(route('admin.kategori.index', ['keparahan' => 'tinggi']));
        $resTinggi->assertSee('Kategori Tinggi Test');
        $resTinggi->assertDontSee('Kategori Rendah Test');
        $resTinggi->assertDontSee('Kategori Sedang Test');
    }

    /**
     * T-12.3 (KF-12.3) & AC 3: Kombinasi pencarian kata kunci dan filter tingkat keparahan.
     * Hasil harus memenuhi kedua kondisi sekaligus.
     */
    public function test_t12_3_kombinasi_pencarian_dan_filter_tingkat_keparahan(): void
    {
        $admin = $this->createAdmin();

        // 1. Cocok nama 'jalan' & Cocok keparahan 'tinggi' -> Harus TAMPIL
        KategoriInfrastruktur::factory()->create([
            'nama_kategori' => 'Jalan Amblas Berbahaya',
            'deskripsi' => 'Kerusakan struktur jalan utama',
            'tingkat_keparahan_default' => 'tinggi',
            'bobot_prioritas' => 3,
        ]);

        // 2. Cocok nama 'jalan' TAPI keparahan 'rendah' -> Tidak boleh tampil
        KategoriInfrastruktur::factory()->create([
            'nama_kategori' => 'Jalan Berdebu',
            'deskripsi' => 'Perlu penyiraman rutin',
            'tingkat_keparahan_default' => 'rendah',
            'bobot_prioritas' => 1,
        ]);

        // 3. Tidak cocok nama 'jalan' TAPI keparahan 'tinggi' -> Tidak boleh tampil
        KategoriInfrastruktur::factory()->create([
            'nama_kategori' => 'Jembatan Retak',
            'deskripsi' => 'Pondasi jembatan mengalami keretakan',
            'tingkat_keparahan_default' => 'tinggi',
            'bobot_prioritas' => 3,
        ]);

        // 4. Tidak cocok keduanya -> Tidak boleh tampil
        KategoriInfrastruktur::factory()->create([
            'nama_kategori' => 'Taman Kota',
            'deskripsi' => 'Rumput liar memanjang',
            'tingkat_keparahan_default' => 'sedang',
            'bobot_prioritas' => 2,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.kategori.index', [
            'search' => 'jalan',
            'keparahan' => 'tinggi',
        ]));

        $response->assertOk();
        $response->assertSee('Jalan Amblas Berbahaya');
        $response->assertDontSee('Jalan Berdebu');
        $response->assertDontSee('Jembatan Retak');
        $response->assertDontSee('Taman Kota');
    }

    /**
     * T-12.4 (KF-12.5) & AC 4: Reset filter menghapus seluruh parameter filter dan menampilkan daftar penuh.
     */
    public function test_t12_4_reset_filter_menampilkan_seluruh_daftar_kategori(): void
    {
        $admin = $this->createAdmin();

        $k1 = KategoriInfrastruktur::factory()->create([
            'nama_kategori' => 'Kategori Pertama',
            'tingkat_keparahan_default' => 'rendah',
            'bobot_prioritas' => 1,
        ]);
        $k2 = KategoriInfrastruktur::factory()->create([
            'nama_kategori' => 'Kategori Kedua',
            'tingkat_keparahan_default' => 'sedang',
            'bobot_prioritas' => 2,
        ]);
        $k3 = KategoriInfrastruktur::factory()->create([
            'nama_kategori' => 'Kategori Ketiga',
            'tingkat_keparahan_default' => 'kritis',
            'bobot_prioritas' => 4,
        ]);

        // Saat ada filter aktif, tombol Reset muncul dengan tautan ke halaman index bersih
        $filteredResponse = $this->actingAs($admin)->get(route('admin.kategori.index', ['keparahan' => 'kritis']));
        $filteredResponse->assertSee('Reset');
        $filteredResponse->assertSee('Kategori Ketiga');
        $filteredResponse->assertDontSee('Kategori Pertama');

        // Ketika mengakses index tanpa filter (efek tombol reset), seluruh data tampil
        $resetResponse = $this->actingAs($admin)->get(route('admin.kategori.index'));
        $resetResponse->assertOk();
        $resetResponse->assertSee($k1->nama_kategori);
        $resetResponse->assertSee($k2->nama_kategori);
        $resetResponse->assertSee($k3->nama_kategori);
    }

    /**
     * T-12.4 (KF-12.6) & AC 5: Menampilkan pesan "Data kategori tidak ditemukan." saat hasil pencarian/filter kosong.
     */
    public function test_t12_4_pesan_kosong_saat_pencarian_tidak_ada_hasil(): void
    {
        $admin = $this->createAdmin();

        KategoriInfrastruktur::factory()->create([
            'nama_kategori' => 'Drainase Perkotaan',
            'tingkat_keparahan_default' => 'tinggi',
            'bobot_prioritas' => 3,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.kategori.index', [
            'search' => 'kata_kunci_yang_sangat_mustahil_ada_12345',
        ]));

        $response->assertOk();
        $response->assertSee('Data kategori tidak ditemukan.');
        $response->assertDontSee('Drainase Perkotaan');
    }

    /**
     * KF-12.4: Hasil paginasi mempertahankan parameter filter pada query string (withQueryString).
     */
    public function test_kf12_4_paginasi_mempertahankan_parameter_query_string(): void
    {
        $admin = $this->createAdmin();

        // Buat 15 kategori yang memenuhi kriteria pencarian "Banjir" agar terjadi paginasi (10 per halaman)
        for ($i = 1; $i <= 15; $i++) {
            KategoriInfrastruktur::factory()->create([
                'nama_kategori' => "Banjir Luapan Titik {$i}",
                'tingkat_keparahan_default' => 'kritis',
                'bobot_prioritas' => 4,
            ]);
        }

        $response = $this->actingAs($admin)->get(route('admin.kategori.index', [
            'search' => 'Banjir',
            'keparahan' => 'kritis',
            'page' => 1,
        ]));

        $response->assertOk();
        // Memastikan link paginasi mengandung query parameter search dan keparahan
        $content = $response->getContent();
        $this->assertStringContainsString('search=Banjir', $content);
        $this->assertStringContainsString('keparahan=kritis', $content);
        $this->assertStringContainsString('page=2', $content);
    }

    /**
     * KF-12.7: Komponen Blade <x-filter-kategori> dapat di-render mandiri dengan props.
     */
    public function test_kf12_7_komponen_filter_kategori_dapat_dirender(): void
    {
        $rendered = $this->blade(
            '<x-filter-kategori :action="$action" :search="$search" :keparahan="$keparahan" />',
            [
                'action' => route('admin.kategori.index'),
                'search' => 'aspal',
                'keparahan' => 'kritis',
            ]
        );

        $rendered->assertSee('name="search"', false);
        $rendered->assertSee('value="aspal"', false);
        $rendered->assertSee('name="keparahan"', false);
        $rendered->assertSee('value="kritis" selected', false);
        $rendered->assertSee('Cari');
        $rendered->assertSee('Reset');
    }

    /**
     * Pengujian Unit/Service KategoriService::getPaginatedKategori secara langsung.
     */
    public function test_kategori_service_get_paginated_kategori_direct(): void
    {
        $service = app(KategoriService::class);

        KategoriInfrastruktur::factory()->create([
            'nama_kategori' => 'Jalan Rusak Berat',
            'deskripsi' => 'Kondisi jalan aspal amblas',
            'tingkat_keparahan_default' => 'kritis',
            'bobot_prioritas' => 4,
        ]);
        KategoriInfrastruktur::factory()->create([
            'nama_kategori' => 'Jalan Rusak Ringan',
            'deskripsi' => 'Kerikil lepas di bahu jalan',
            'tingkat_keparahan_default' => 'rendah',
            'bobot_prioritas' => 1,
        ]);

        // Uji filter service: cari "rusak" + tingkat "kritis"
        $results = $service->getPaginatedKategori(search: 'rusak', keparahan: 'kritis', perPage: 10);
        $this->assertEquals(1, $results->total());
        $this->assertEquals('Jalan Rusak Berat', $results->first()->nama_kategori);

        // Uji filter service: keparahan "semua" -> mengembalikan seluruh data
        $resultsSemua = $service->getPaginatedKategori(search: 'rusak', keparahan: 'semua', perPage: 10);
        $this->assertEquals(2, $resultsSemua->total());
    }
}

