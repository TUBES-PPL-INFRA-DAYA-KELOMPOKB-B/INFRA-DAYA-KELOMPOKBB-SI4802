<?php

namespace Tests\Browser;

use App\Models\KategoriInfrastruktur;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/**
 * Fase 3: Pengujian E2E Master Kategori Infrastruktur (Admin).
 */
class KategoriTest extends DuskTestCase
{
    use DatabaseMigrations;

    private function loginAsAdmin(Browser $browser): User
    {
        $admin = User::factory()->create([
            'email' => 'admin.kat.'.uniqid().'@dsdabm.bandung.go.id',
            'role' => 'admin',
        ]);
        $browser->loginAs($admin);

        return $admin;
    }

    /**
     * Admin dapat melihat daftar kategori infrastruktur.
     */
    public function test_admin_can_view_kategori_index(): void
    {
        KategoriInfrastruktur::factory()->create([
            'nama_kategori' => 'Kategori Uji Coba',
            'bobot_prioritas' => 3,
        ]);

        $this->browse(function (Browser $browser) {
            $this->loginAsAdmin($browser);

            $browser->visit(route('admin.kategori.index'))
                ->assertSee('Kategori Infrastruktur')
                ->assertSee('Kategori Uji Coba')
                ->assertPresent('table');
        });
    }

    /**
     * Admin dapat menambahkan kategori baru.
     */
    public function test_admin_can_create_new_kategori(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsAdmin($browser);

            $browser->visit(route('admin.kategori.create'))
                ->type('nama_kategori', 'Jembatan Penyeberangan Orang (JPO)')
                ->select('tingkat_keparahan_default', 'sedang')
                ->type('deskripsi', 'Kerusakan lantai plat JPO atau atap bocor')
                ->click('@submit-btn')
                ->waitForLocation('/admin/kategori')
                ->assertSee('berhasil ditambahkan')
                ->assertSee('Jembatan Penyeberangan Orang (JPO)');
        });
    }

    /**
     * Validasi: nama kategori wajib diisi.
     */
    public function test_kategori_creation_requires_nama(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsAdmin($browser);

            $browser->visit(route('admin.kategori.create'))
                ->click('@submit-btn')
                ->assertPathIs('/admin/kategori/create');
        });
    }

    /**
     * Admin dapat mengubah kategori infrastruktur.
     */
    public function test_admin_can_edit_kategori(): void
    {
        $kategori = KategoriInfrastruktur::factory()->create([
            'nama_kategori' => 'Kategori Sebelum Edit',
            'bobot_prioritas' => 1,
        ]);

        $this->browse(function (Browser $browser) use ($kategori) {
            $this->loginAsAdmin($browser);

            $browser->visit(route('admin.kategori.edit', $kategori->id))
                ->clear('nama_kategori')
                ->type('nama_kategori', 'Kategori Setelah Edit')
                ->select('tingkat_keparahan_default', 'tinggi')
                ->click('@submit-btn')
                ->waitForLocation('/admin/kategori')
                ->assertSee('berhasil diperbarui')
                ->assertSee('Kategori Setelah Edit');
        });
    }

    /**
     * Non-admin (warga) dilarang mengakses kelola kategori (403).
     */
    public function test_non_admin_cannot_access_kategori(): void
    {
        $warga = User::factory()->create([
            'email' => 'warga.kat.'.uniqid().'@test.com',
            'role' => 'warga',
        ]);

        $this->browse(function (Browser $browser) use ($warga) {
            $browser->loginAs($warga)
                ->visit(route('admin.kategori.index'))
                ->assertSee('403');
        });
    }
}
