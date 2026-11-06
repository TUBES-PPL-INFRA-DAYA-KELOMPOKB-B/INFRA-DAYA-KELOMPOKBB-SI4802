<?php

namespace Tests\Browser;

use App\Models\Kecamatan;
use App\Models\Kelurahan;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/**
 * Fase 1: Pengujian E2E CRUD Master Data Wilayah (Kecamatan & Kelurahan).
 */
class WilayahTest extends DuskTestCase
{
    use DatabaseMigrations;

    /** Helper: buat admin dan login langsung di browser */
    private function loginAsAdmin(Browser $browser): User
    {
        $admin = User::factory()->create([
            'email' => 'admin.'.uniqid().'@dsdabm.bandung.go.id',
            'role' => 'admin',
        ]);
        $browser->loginAs($admin);

        return $admin;
    }

    // -------------------------------------------------------------------------
    // KECAMATAN
    // -------------------------------------------------------------------------

    /**
     * Admin dapat melihat halaman daftar kecamatan (heading "Kelola Data Kecamatan").
     */
    public function test_admin_can_view_kecamatan_index(): void
    {
        // Buat 1 record agar tabel dirender (bukan empty-state)
        Kecamatan::factory()->create([
            'kode_kecamatan' => 'KEC-VIEW-01',
            'nama_kecamatan' => 'Kecamatan Tampil',
        ]);

        $this->browse(function (Browser $browser) {
            $this->loginAsAdmin($browser);

            $browser->visit(route('admin.kecamatan.index'))
                ->assertSee('Kelola Data Kecamatan')
                ->assertSee('Kecamatan Tampil')
                ->assertPresent('table');
        });
    }

    /**
     * Admin dapat menambahkan kecamatan baru.
     */
    public function test_admin_can_create_kecamatan(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsAdmin($browser);

            $browser->visit(route('admin.kecamatan.create'))
                ->type('kode_kecamatan', 'KEC-TEST-01')
                ->type('nama_kecamatan', 'Kecamatan Uji Coba')
                ->click('@submit-btn')
                ->waitForLocation('/admin/kecamatan')
                ->assertSee('berhasil ditambahkan');
        });
    }

    /**
     * Validasi: kode_kecamatan tidak boleh kosong (form tidak submit / tetap di halaman create).
     */
    public function test_kecamatan_creation_requires_kode(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsAdmin($browser);

            $browser->visit(route('admin.kecamatan.create'))
                ->type('nama_kecamatan', 'Tanpa Kode')
                ->press('Simpan Kecamatan')
                ->assertDontSee('berhasil ditambahkan');
        });
    }

    /**
     * Admin dapat mengedit kecamatan yang ada.
     */
    public function test_admin_can_edit_kecamatan(): void
    {
        $kecamatan = Kecamatan::factory()->create([
            'kode_kecamatan' => 'KEC-EDIT-01',
            'nama_kecamatan' => 'Kecamatan Edit Lama',
        ]);

        $this->browse(function (Browser $browser) use ($kecamatan) {
            $this->loginAsAdmin($browser);

            $browser->visit(route('admin.kecamatan.edit', $kecamatan->id))
                ->clear('nama_kecamatan')
                ->type('nama_kecamatan', 'Kecamatan Edit Baru')
                ->click('@submit-btn')
                ->waitForLocation('/admin/kecamatan')
                ->assertSee('berhasil diperbarui');
        });
    }

    /**
     * Admin dapat menghapus kecamatan melalui form tersembunyi.
     */
    public function test_admin_can_delete_kecamatan(): void
    {
        $kecamatan = Kecamatan::factory()->create([
            'kode_kecamatan' => 'KEC-DEL-01',
            'nama_kecamatan' => 'Kecamatan Hapus',
        ]);

        $this->browse(function (Browser $browser) use ($kecamatan) {
            $this->loginAsAdmin($browser);

            $browser->visit(route('admin.kecamatan.index'))
                ->assertSee('Kecamatan Hapus');

            // Submit form hapus tersembunyi (bypass Alpine.js modal untuk Dusk)
            $browser->script(
                "document.getElementById('delete-form-{$kecamatan->id}').submit();"
            );

            $browser->assertPathIs('/admin/kecamatan')
                ->assertSee('berhasil dihapus');
        });
    }

    /**
     * Fitur pencarian kecamatan berfungsi.
     */
    public function test_kecamatan_search_works(): void
    {
        Kecamatan::factory()->create(['kode_kecamatan' => 'KEC-SRC-01', 'nama_kecamatan' => 'Bandung Barat']);
        Kecamatan::factory()->create(['kode_kecamatan' => 'KEC-SRC-02', 'nama_kecamatan' => 'Bandung Timur']);

        $this->browse(function (Browser $browser) {
            $this->loginAsAdmin($browser);

            $browser->visit(route('admin.kecamatan.index'))
                ->type('input[name="search"]', 'Barat')
                ->press('Cari')
                ->waitForText('Bandung Barat')
                ->assertSee('Bandung Barat')
                ->assertDontSee('Bandung Timur');
        });
    }

    // -------------------------------------------------------------------------
    // KELURAHAN
    // -------------------------------------------------------------------------

    /**
     * Admin dapat melihat halaman daftar kelurahan (heading "Kelola Data Kelurahan").
     */
    public function test_admin_can_view_kelurahan_index(): void
    {
        // Buat 1 record agar tabel dirender (bukan empty-state)
        $kec = Kecamatan::factory()->create([
            'kode_kecamatan' => 'KEC-VKEL-01',
            'nama_kecamatan' => 'Kecamatan Lihat',
        ]);
        Kelurahan::factory()->create([
            'kecamatan_id' => $kec->id,
            'kode_kelurahan' => 'KEL-VIEW-01',
            'nama_kelurahan' => 'Kelurahan Tampil',
        ]);

        $this->browse(function (Browser $browser) {
            $this->loginAsAdmin($browser);

            $browser->visit(route('admin.kelurahan.index'))
                ->assertSee('Kelola Data Kelurahan')
                ->assertSee('Kelurahan Tampil')
                ->assertPresent('table');
        });
    }

    /**
     * Admin dapat menambahkan kelurahan baru.
     */
    public function test_admin_can_create_kelurahan(): void
    {
        $kecamatan = Kecamatan::factory()->create([
            'kode_kecamatan' => 'KEC-KEL-01',
            'nama_kecamatan' => 'Kecamatan Induk',
        ]);

        $this->browse(function (Browser $browser) use ($kecamatan) {
            $this->loginAsAdmin($browser);

            $browser->visit(route('admin.kelurahan.create'))
                ->select('kecamatan_id', $kecamatan->id)
                ->type('kode_kelurahan', 'KEL-TEST-01')
                ->type('nama_kelurahan', 'Kelurahan Uji Coba')
                ->click('@submit-btn')
                ->waitForLocation('/admin/kelurahan')
                ->assertSee('berhasil ditambahkan');
        });
    }

    /**
     * Admin dapat mengedit kelurahan yang ada.
     */
    public function test_admin_can_edit_kelurahan(): void
    {
        $kecamatan = Kecamatan::factory()->create([
            'kode_kecamatan' => 'KEC-EKEL-01',
            'nama_kecamatan' => 'Kecamatan Edit Kelurahan',
        ]);
        $kelurahan = Kelurahan::factory()->create([
            'kecamatan_id' => $kecamatan->id,
            'kode_kelurahan' => 'KEL-EDIT-01',
            'nama_kelurahan' => 'Kelurahan Lama',
        ]);

        $this->browse(function (Browser $browser) use ($kelurahan) {
            $this->loginAsAdmin($browser);

            $browser->visit(route('admin.kelurahan.edit', $kelurahan->id))
                ->clear('nama_kelurahan')
                ->type('nama_kelurahan', 'Kelurahan Baru')
                ->click('@submit-btn')
                ->waitForLocation('/admin/kelurahan')
                ->assertSee('berhasil diperbarui');
        });
    }

    /**
     * API endpoint chained dropdown kelurahan-by-kecamatan mengembalikan JSON valid.
     */
    public function test_api_kelurahan_by_kecamatan_returns_json(): void
    {
        $kecamatan = Kecamatan::factory()->create([
            'kode_kecamatan' => 'KEC-API-01',
            'nama_kecamatan' => 'Kecamatan API Test',
        ]);
        Kelurahan::factory()->count(3)->create(['kecamatan_id' => $kecamatan->id]);

        $this->browse(function (Browser $browser) use ($kecamatan) {
            $browser->visit(route('api.wilayah.kelurahan', $kecamatan->id))
                ->assertSee('"kecamatan_id"');
        });
    }

    /**
     * Non-admin (warga) mendapat respons 403 saat mengakses halaman admin wilayah.
     */
    public function test_non_admin_cannot_access_wilayah_admin(): void
    {
        $warga = User::factory()->create([
            'email' => 'warga.wilayah.'.uniqid().'@test.com',
            'role' => 'warga',
        ]);

        $this->browse(function (Browser $browser) use ($warga) {
            $browser->loginAs($warga)
                ->visit(route('admin.kecamatan.index'))
                ->assertSee('403');
        });
    }
}
