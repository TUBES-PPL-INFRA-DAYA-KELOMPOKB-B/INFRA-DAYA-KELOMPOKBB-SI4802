<?php

namespace Tests\Browser;

use App\Models\User;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/**
 * TC01-07: Pilih Hapus pada U1 -> konfirmasi penghapusan (U1 belum memiliki transaksi)
 * Expected: Akun U1 berhasil dihapus dari database dan hilang dari daftar pengguna
 */
class TC01_07_Test extends DuskTestCase
{
    public function test_tc01_07(): void
    {
        $admin = User::factory()->create([
            'email' => 'admin.tc0107.'.uniqid().'@dsdabm.bandung.go.id',
            'role' => 'admin',
            'is_active' => true,
        ]);

        $u1 = User::factory()->create([
            'name' => 'Budi Siap Hapus',
            'email' => 'budi.hapus.'.uniqid().'@bandung.go.id',
            'role' => 'warga',
        ]);

        $this->browse(function (Browser $browser) use ($admin, $u1) {
            $browser->loginAs($admin)
                ->visit(route('admin.users.index', ['search' => $u1->name]))
                ->waitFor("@delete-btn-{$u1->id}")
                ->assertSee($u1->name)
                ->click("@delete-btn-{$u1->id}")
                ->waitFor('#deleteModal')
                ->click('@confirm-delete-btn')
                ->waitForLocation('/admin/users')
                ->assertSee('Pengguna berhasil dihapus');
        });

        $this->assertDatabaseMissing('users', ['id' => $u1->id]);
    }
}
