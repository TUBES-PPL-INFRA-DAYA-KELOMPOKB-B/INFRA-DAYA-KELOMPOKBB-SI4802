<?php

namespace Tests\Browser;

use App\Models\User;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/**
 * TC01-06: Pilih Ubah pada U1 -> perbarui nama & nomor telepon -> Simpan
 * Input: Nama: Budi S. Teknik, Telp: 081299990000
 * Expected: Perubahan tersimpan di UI dan database
 */
class TC01_06_Test extends DuskTestCase
{
    public function test_tc01_06(): void
    {
        $admin = User::factory()->create([
            'email' => 'admin.tc0106.'.uniqid().'@dsdabm.bandung.go.id',
            'role' => 'admin',
            'is_active' => true,
        ]);

        $u1 = User::factory()->create([
            'name' => 'Budi Santoso Awal',
            'email' => 'budi.edit.'.uniqid().'@bandung.go.id',
            'phone' => '0811112222',
            'role' => 'petugas',
        ]);

        $this->browse(function (Browser $browser) use ($admin, $u1) {
            $browser->loginAs($admin)
                ->visit(route('admin.users.edit', $u1->id))
                ->clear('name')
                ->type('name', 'Budi S. Teknik')
                ->clear('phone')
                ->type('phone', '081299990000')
                ->click('@submit-btn')
                ->waitForLocation('/admin/users')
                ->assertSee('berhasil diperbarui')
                ->assertSee('Budi S. Teknik');
        });

        $u1->refresh();
        $this->assertEquals('Budi S. Teknik', $u1->name);
        $this->assertEquals('081299990000', $u1->phone);
    }
}
