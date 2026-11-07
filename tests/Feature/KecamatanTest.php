<?php

namespace Tests\Feature;

use App\Models\Kecamatan;
use App\Models\Kelurahan;
use App\Models\User;
use Database\Seeders\WilayahSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KecamatanTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $warga;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'email' => 'admin.test@dsdabm.bandung.go.id',
            'role' => 'admin',
        ]);

        $this->warga = User::factory()->create([
            'email' => 'warga.test@bandung.go.id',
            'role' => 'warga',
        ]);
    }

    /**
     * FR-01-01: Admin dapat melihat daftar kecamatan, urut nama, dan ada jumlah kelurahan.
     */
    public function test_admin_can_view_kecamatan_list_sorted_by_name_with_kelurahan_count(): void
    {
        $kecZ = Kecamatan::factory()->create([
            'kode_kecamatan' => '327330',
            'nama_kecamatan' => 'Z-Kecamatan',
        ]);
        $kecA = Kecamatan::factory()->create([
            'kode_kecamatan' => '327301',
            'nama_kecamatan' => 'A-Kecamatan',
        ]);

        // Berikan kelurahan ke kecA
        Kelurahan::factory()->count(2)->create([
            'kecamatan_id' => $kecA->id,
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.kecamatan.index'));

        $response->assertOk();
        $response->assertViewIs('admin.wilayah.kecamatan.index');
        $response->assertSeeInOrder(['A-Kecamatan', 'Z-Kecamatan']);
        $response->assertSee('2'); // Kelurahan count
        $response->assertSee('327301');
    }

    /**
     * FR-01-02: Admin dapat menambah data kecamatan dengan validasi kode dan nama.
     */
    public function test_admin_can_create_kecamatan_with_valid_data(): void
    {
        $payload = [
            'kode_kecamatan' => '327301',
            'nama_kecamatan' => 'Andir',
        ];

        $response = $this->actingAs($this->admin)->post(route('admin.kecamatan.store'), $payload);

        $response->assertRedirect(route('admin.kecamatan.index'));
        $response->assertSessionHas('success', 'Data kecamatan baru berhasil ditambahkan.');
        $this->assertDatabaseHas('kecamatan', [
            'kode_kecamatan' => '327301',
            'nama_kecamatan' => 'Andir',
        ]);
    }

    /**
     * FR-01-05: Validasi field kode_kecamatan dan nama_kecamatan wajib diisi.
     */
    public function test_create_kecamatan_validation_required_fields(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.kecamatan.store'), [
            'kode_kecamatan' => '',
            'nama_kecamatan' => '',
        ]);

        $response->assertSessionHasErrors(['kode_kecamatan', 'nama_kecamatan']);
    }

    /**
     * FR-01-05: Validasi kode_kecamatan harus unik.
     */
    public function test_create_kecamatan_validation_unique_kode(): void
    {
        Kecamatan::factory()->create([
            'kode_kecamatan' => '327301',
            'nama_kecamatan' => 'Andir Lama',
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.kecamatan.store'), [
            'kode_kecamatan' => '327301',
            'nama_kecamatan' => 'Andir Baru',
        ]);

        $response->assertSessionHasErrors(['kode_kecamatan']);
        $this->assertDatabaseCount('kecamatan', 1);
    }

    /**
     * FR-01-02 & Model Data: kode_kecamatan maksimal 10 karakter.
     */
    public function test_create_kecamatan_validation_max_length_10_characters(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.kecamatan.store'), [
            'kode_kecamatan' => '12345678901', // 11 karakter
            'nama_kecamatan' => 'Test Max Length',
        ]);

        $response->assertSessionHasErrors(['kode_kecamatan']);
    }

    /**
     * FR-01-03: Admin dapat mengubah kode dan nama kecamatan.
     */
    public function test_admin_can_update_kecamatan(): void
    {
        $kecamatan = Kecamatan::factory()->create([
            'kode_kecamatan' => '327301',
            'nama_kecamatan' => 'Andir Awal',
        ]);

        $payload = [
            'kode_kecamatan' => '327301',
            'nama_kecamatan' => 'Andir Diperbarui',
        ];

        $response = $this->actingAs($this->admin)->put(route('admin.kecamatan.update', $kecamatan->id), $payload);

        $response->assertRedirect(route('admin.kecamatan.index'));
        $response->assertSessionHas('success', 'Data kecamatan berhasil diperbarui.');
        $this->assertDatabaseHas('kecamatan', [
            'id' => $kecamatan->id,
            'kode_kecamatan' => '327301',
            'nama_kecamatan' => 'Andir Diperbarui',
        ]);
    }

    /**
     * FR-01-04: Penghapusan kecamatan ditolak jika masih memiliki kelurahan.
     */
    public function test_admin_cannot_delete_kecamatan_with_kelurahan(): void
    {
        $kecamatan = Kecamatan::factory()->create([
            'kode_kecamatan' => '327301',
            'nama_kecamatan' => 'Andir',
        ]);

        Kelurahan::factory()->create([
            'kecamatan_id' => $kecamatan->id,
            'kode_kelurahan' => '3273011001',
            'nama_kelurahan' => 'Campaka',
        ]);

        $response = $this->actingAs($this->admin)->delete(route('admin.kecamatan.destroy', $kecamatan->id));

        $response->assertRedirect(route('admin.kecamatan.index'));
        $response->assertSessionHas('error', 'Kecamatan tidak dapat dihapus karena masih memiliki data kelurahan.');
        $this->assertDatabaseHas('kecamatan', ['id' => $kecamatan->id]);
        $this->assertDatabaseHas('kelurahan', ['kode_kelurahan' => '3273011001']);
    }

    /**
     * FR-01-04: Penghapusan kecamatan berhasil jika tidak memiliki kelurahan.
     */
    public function test_admin_can_delete_kecamatan_without_kelurahan(): void
    {
        $kecamatan = Kecamatan::factory()->create([
            'kode_kecamatan' => '327399',
            'nama_kecamatan' => 'Kecamatan Kosong',
        ]);

        $response = $this->actingAs($this->admin)->delete(route('admin.kecamatan.destroy', $kecamatan->id));

        $response->assertRedirect(route('admin.kecamatan.index'));
        $response->assertSessionHas('success', 'Data kecamatan berhasil dihapus.');
        $this->assertDatabaseMissing('kecamatan', ['id' => $kecamatan->id]);
    }

    /**
     * FR-01-01: Filter/pencarian data kecamatan.
     */
    public function test_admin_can_search_kecamatan(): void
    {
        Kecamatan::factory()->create(['kode_kecamatan' => '327301', 'nama_kecamatan' => 'Andir']);
        Kecamatan::factory()->create(['kode_kecamatan' => '327302', 'nama_kecamatan' => 'Astana Anyar']);

        $response = $this->actingAs($this->admin)->get(route('admin.kecamatan.index', ['search' => 'Andir']));

        $response->assertOk();
        $response->assertSee('Andir');
        $response->assertDontSee('Astana Anyar');
    }

    /**
     * NFR-02: Pengguna non-admin ditolak saat mengakses halaman CRUD kecamatan.
     */
    public function test_non_admin_cannot_access_kecamatan_crud(): void
    {
        $this->actingAs($this->warga)
            ->get(route('admin.kecamatan.index'))
            ->assertForbidden();

        $this->actingAs($this->warga)
            ->get(route('admin.kecamatan.create'))
            ->assertForbidden();

        $this->actingAs($this->warga)
            ->post(route('admin.kecamatan.store'), ['kode_kecamatan' => '123', 'nama_kecamatan' => 'Test'])
            ->assertForbidden();
    }

    /**
     * AC / FR-03-01: Seeder menghasilkan 30 kecamatan di Kota Bandung.
     */
    public function test_seeder_creates_30_kecamatan(): void
    {
        $this->seed(WilayahSeeder::class);

        $this->assertDatabaseCount('kecamatan', 30);
    }
}

