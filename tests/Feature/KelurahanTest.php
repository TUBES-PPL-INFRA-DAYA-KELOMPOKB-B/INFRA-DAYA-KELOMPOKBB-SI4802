<?php

namespace Tests\Feature;

use App\Models\KategoriInfrastruktur;
use App\Models\Kecamatan;
use App\Models\Kelurahan;
use App\Models\LaporanKerusakan;
use App\Models\User;
use Database\Seeders\WilayahSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KelurahanTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $warga;
    protected Kecamatan $kecamatan;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'email' => 'admin.kelurahan@dsdabm.bandung.go.id',
            'role' => 'admin',
        ]);

        $this->warga = User::factory()->create([
            'email' => 'warga.kelurahan@bandung.go.id',
            'role' => 'warga',
        ]);

        $this->kecamatan = Kecamatan::factory()->create([
            'kode_kecamatan' => '327301',
            'nama_kecamatan' => 'Andir',
        ]);
    }

    /**
     * FR-02-01: Admin dapat melihat daftar kelurahan dengan kode, nama, dan nama kecamatan induk.
     */
    public function test_admin_can_view_kelurahan_index_with_parent_kecamatan(): void
    {
        $kelurahan = Kelurahan::factory()->create([
            'kecamatan_id' => $this->kecamatan->id,
            'kode_kelurahan' => '3273011001',
            'nama_kelurahan' => 'Campaka',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.kelurahan.index'));

        $response->assertOk();
        $response->assertViewIs('admin.wilayah.kelurahan.index');
        $response->assertSee('Campaka');
        $response->assertSee('3273011001');
        $response->assertSee('Andir');
    }

    /**
     * FR-02-01: Admin dapat memfilter daftar kelurahan berdasarkan kecamatan induk.
     */
    public function test_admin_can_filter_kelurahan_by_kecamatan(): void
    {
        $kecLain = Kecamatan::factory()->create([
            'kode_kecamatan' => '327302',
            'nama_kecamatan' => 'Astana Anyar',
        ]);

        Kelurahan::factory()->create([
            'kecamatan_id' => $this->kecamatan->id,
            'kode_kelurahan' => '3273011001',
            'nama_kelurahan' => 'Campaka',
        ]);

        Kelurahan::factory()->create([
            'kecamatan_id' => $kecLain->id,
            'kode_kelurahan' => '3273021001',
            'nama_kelurahan' => 'Cibadak',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.kelurahan.index', [
            'kecamatan_id' => $this->kecamatan->id,
        ]));

        $response->assertOk();
        $response->assertSee('Campaka');
        $response->assertDontSee('Cibadak');
    }

    /**
     * FR-02-01: Admin dapat mencari kelurahan berdasarkan nama atau kode.
     */
    public function test_admin_can_search_kelurahan(): void
    {
        Kelurahan::factory()->create([
            'kecamatan_id' => $this->kecamatan->id,
            'kode_kelurahan' => '3273011001',
            'nama_kelurahan' => 'Campaka',
        ]);

        Kelurahan::factory()->create([
            'kecamatan_id' => $this->kecamatan->id,
            'kode_kelurahan' => '3273011002',
            'nama_kelurahan' => 'Ciroyom',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.kelurahan.index', [
            'search' => 'Campaka',
        ]));

        $response->assertOk();
        $response->assertSee('Campaka');
        $response->assertDontSee('Ciroyom');
    }

    /**
     * FR-02-02: Admin dapat menambah kelurahan baru dengan kecamatan_id, kode, dan nama.
     */
    public function test_admin_can_create_kelurahan_with_valid_data(): void
    {
        $payload = [
            'kecamatan_id' => $this->kecamatan->id,
            'kode_kelurahan' => '3273011001',
            'nama_kelurahan' => 'Campaka',
        ];

        $response = $this->actingAs($this->admin)->post(route('admin.kelurahan.store'), $payload);

        $response->assertRedirect(route('admin.kelurahan.index'));
        $response->assertSessionHas('success', 'Data kelurahan baru berhasil ditambahkan.');
        $this->assertDatabaseHas('kelurahan', [
            'kecamatan_id' => $this->kecamatan->id,
            'kode_kelurahan' => '3273011001',
            'nama_kelurahan' => 'Campaka',
        ]);
    }

    /**
     * FR-02-05: Validasi field kecamatan_id, kode_kelurahan, dan nama_kelurahan wajib diisi.
     */
    public function test_create_kelurahan_validation_required_fields(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.kelurahan.store'), [
            'kecamatan_id' => '',
            'kode_kelurahan' => '',
            'nama_kelurahan' => '',
        ]);

        $response->assertSessionHasErrors(['kecamatan_id', 'kode_kelurahan', 'nama_kelurahan']);
    }

    /**
     * FR-02-05: Validasi kecamatan_id harus valid (ada di tabel kecamatan).
     */
    public function test_create_kelurahan_validation_kecamatan_must_exist(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.kelurahan.store'), [
            'kecamatan_id' => 99999,
            'kode_kelurahan' => '3273011001',
            'nama_kelurahan' => 'Campaka',
        ]);

        $response->assertSessionHasErrors(['kecamatan_id']);
    }

    /**
     * FR-02-05: Validasi kode_kelurahan harus unik dalam sistem.
     */
    public function test_create_kelurahan_validation_unique_kode(): void
    {
        Kelurahan::factory()->create([
            'kecamatan_id' => $this->kecamatan->id,
            'kode_kelurahan' => '3273011001',
            'nama_kelurahan' => 'Campaka Lama',
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.kelurahan.store'), [
            'kecamatan_id' => $this->kecamatan->id,
            'kode_kelurahan' => '3273011001',
            'nama_kelurahan' => 'Campaka Baru',
        ]);

        $response->assertSessionHasErrors(['kode_kelurahan']);
        $this->assertDatabaseCount('kelurahan', 1);
    }

    /**
     * FR-02-02 & Model Data: kode_kelurahan maksimal 10 karakter.
     */
    public function test_create_kelurahan_validation_max_length_10_characters(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.kelurahan.store'), [
            'kecamatan_id' => $this->kecamatan->id,
            'kode_kelurahan' => '32730110001', // 11 karakter
            'nama_kelurahan' => 'Campaka Test',
        ]);

        $response->assertSessionHasErrors(['kode_kelurahan']);
    }

    /**
     * FR-02-03: Admin dapat mengubah kode, nama, dan kecamatan induk kelurahan.
     */
    public function test_admin_can_update_kelurahan(): void
    {
        $kecBaru = Kecamatan::factory()->create([
            'kode_kecamatan' => '327302',
            'nama_kecamatan' => 'Astana Anyar',
        ]);

        $kelurahan = Kelurahan::factory()->create([
            'kecamatan_id' => $this->kecamatan->id,
            'kode_kelurahan' => '3273011001',
            'nama_kelurahan' => 'Campaka Awal',
        ]);

        $payload = [
            'kecamatan_id' => $kecBaru->id,
            'kode_kelurahan' => '3273011001',
            'nama_kelurahan' => 'Campaka Diperbarui',
        ];

        $response = $this->actingAs($this->admin)->put(route('admin.kelurahan.update', $kelurahan->id), $payload);

        $response->assertRedirect(route('admin.kelurahan.index'));
        $response->assertSessionHas('success', 'Data kelurahan berhasil diperbarui.');
        $this->assertDatabaseHas('kelurahan', [
            'id' => $kelurahan->id,
            'kecamatan_id' => $kecBaru->id,
            'kode_kelurahan' => '3273011001',
            'nama_kelurahan' => 'Campaka Diperbarui',
        ]);
    }

    /**
     * FR-02-04: Penghapusan kelurahan ditolak jika kelurahan sudah dipakai oleh laporan kerusakan.
     */
    public function test_admin_cannot_delete_kelurahan_used_by_laporan(): void
    {
        $kelurahan = Kelurahan::factory()->create([
            'kecamatan_id' => $this->kecamatan->id,
            'kode_kelurahan' => '3273011001',
            'nama_kelurahan' => 'Campaka',
        ]);

        $kategori = KategoriInfrastruktur::factory()->create();

        // Buat laporan kerusakan yang menggunakan kelurahan ini
        LaporanKerusakan::create([
            'nomor_tiket' => 'INFRA-20261107-0001',
            'warga_id' => $this->warga->id,
            'kelurahan_id' => $kelurahan->id,
            'kategori_id' => $kategori->id,
            'judul' => 'Jalan Rusak Berlubang',
            'deskripsi_kondisi' => 'Lubang jalan sedalam 15cm di jalan utama',
            'latitude' => -6.914744,
            'longitude' => 107.609810,
            'foto_bukti_sebelum' => 'laporan/bukti_sebelum.jpg',
            'status' => 'menunggu_verifikasi',
            'prioritas' => 'sedang',
        ]);

        $response = $this->actingAs($this->admin)->delete(route('admin.kelurahan.destroy', $kelurahan->id));

        $response->assertRedirect(route('admin.kelurahan.index'));
        $response->assertSessionHas('error', 'Kelurahan tidak dapat dihapus karena masih digunakan dalam laporan kerusakan.');
        $this->assertDatabaseHas('kelurahan', ['id' => $kelurahan->id]);
    }

    /**
     * FR-02-04: Penghapusan kelurahan berhasil jika belum dipakai oleh laporan kerusakan.
     */
    public function test_admin_can_delete_kelurahan_without_laporan(): void
    {
        $kelurahan = Kelurahan::factory()->create([
            'kecamatan_id' => $this->kecamatan->id,
            'kode_kelurahan' => '3273011099',
            'nama_kelurahan' => 'Kelurahan Baru Belum Dipakai',
        ]);

        $response = $this->actingAs($this->admin)->delete(route('admin.kelurahan.destroy', $kelurahan->id));

        $response->assertRedirect(route('admin.kelurahan.index'));
        $response->assertSessionHas('success', 'Data kelurahan berhasil dihapus.');
        $this->assertDatabaseMissing('kelurahan', ['id' => $kelurahan->id]);
    }

    /**
     * NFR-02: Pengguna non-admin dilarang mengakses halaman CRUD kelurahan.
     */
    public function test_non_admin_cannot_access_kelurahan_crud(): void
    {
        $this->actingAs($this->warga)
            ->get(route('admin.kelurahan.index'))
            ->assertForbidden();

        $this->actingAs($this->warga)
            ->get(route('admin.kelurahan.create'))
            ->assertForbidden();

        $this->actingAs($this->warga)
            ->post(route('admin.kelurahan.store'), [
                'kecamatan_id' => $this->kecamatan->id,
                'kode_kelurahan' => '3273011001',
                'nama_kelurahan' => 'Campaka',
            ])
            ->assertForbidden();
    }

    /**
     * AC Bagian 12: Seeder menghasilkan 151 kelurahan.
     */
    public function test_seeder_creates_151_kelurahan(): void
    {
        $this->seed(WilayahSeeder::class);

        $this->assertDatabaseCount('kelurahan', 151);
    }
}

