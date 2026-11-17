<?php

namespace Tests\Browser;

use App\Models\User;
use App\Services\UserLogService;
use App\Services\UserService;
use Laravel\Dusk\Browser;
use Mockery;
use Tests\DuskTestCase;

/**
 * TC-LOG-05: A1 login; simulasi gagal simpan log.
 * Langkah: Ubah status K1 saat tabel log mengalami kegagalan tulis (fault injection).
 * Expected: Transaksi di-rollback; status K1 tidak berubah menjadi nonaktif.
 */
class TC_LOG_05_Test extends DuskTestCase
{
    public function test_tc_log_05(): void
    {
        $a1 = User::factory()->create([
            'email' => 'admin.tclog05.'.uniqid().'@dsdabm.bandung.go.id',
            'role' => 'admin',
            'is_active' => true,
        ]);

        $k1 = User::factory()->create([
            'name' => 'Karyawan K1 Rollback Test',
            'email' => 'k1.rollback.'.uniqid().'@dsdabm.bandung.go.id',
            'role' => 'petugas',
            'is_active' => true,
        ]);

        // Mock UserLogService untuk melempar exception (simulasi kegagalan tulis log audit)
        $mockLogService = Mockery::mock(UserLogService::class);
        $mockLogService->shouldReceive('log')
            ->andThrow(new \RuntimeException('Database disk full / log write failure simulation'));

        $this->app->instance(UserLogService::class, $mockLogService);

        $userService = app(UserService::class);

        // Eksekusi perubahan status: harus melempar exception dan me-rollback perubahan
        try {
            $userService->toggleUserStatus($k1->id, 'Testing kegagalan log');
            $this->fail('Harusnya melempar RuntimeException saat log gagal simpan.');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('log write failure simulation', $e->getMessage());
        }

        // Verifikasi atomisitas: status K1 di database TIDAK berubah (tetap is_active = 1)
        $this->assertDatabaseHas('users', [
            'id' => $k1->id,
            'is_active' => 1,
        ]);

        // Verifikasi melalui antarmuka Dusk bahwa K1 tetap aktif
        $this->browse(function (Browser $browser) use ($a1, $k1) {
            $browser->loginAs($a1)
                ->visit(route('admin.users.index', ['search' => $k1->name]))
                ->waitForText($k1->name)
                ->assertSee($k1->name)
                ->assertSee('Aktif');
        });
    }
}
