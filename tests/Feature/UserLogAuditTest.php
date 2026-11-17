<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserLog;
use App\Services\UserLogService;
use App\Services\UserService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserLogAuditTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * AC 6.3: Sistem mencatat log pembuatan akun pengguna baru.
     */
    public function test_user_creation_is_logged(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

        $userService = app(UserService::class);
        $newUser = $this->actingAs($admin)->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->post(route('admin.users.store'), [
                'name' => 'Budi Santoso',
                'email' => 'budi.santoso@bandung.go.id',
                'phone' => '081234567890',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'role' => 'petugas',
            ]);

        $createdUser = User::where('email', 'budi.santoso@bandung.go.id')->first();
        $this->assertNotNull($createdUser);

        $this->assertDatabaseHas('user_logs', [
            'action' => 'user_created',
            'target_user_id' => $createdUser->id,
            'actor_id' => $admin->id,
        ]);
    }

    /**
     * AC 6.3: Sistem mencatat pembaruan data dan perubahan role akun.
     */
    public function test_user_update_and_role_change_are_logged(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $targetUser = User::factory()->create(['role' => 'warga', 'name' => 'Nama Lama']);

        $response = $this->actingAs($admin)->put(route('admin.users.update', $targetUser->id), [
            'name' => 'Nama Baru',
            'email' => $targetUser->email,
            'phone' => '0899999999',
            'role' => 'petugas',
        ]);

        $response->assertRedirect(route('admin.users.index'));

        // Cek log pembaruan data
        $this->assertDatabaseHas('user_logs', [
            'action' => 'user_updated',
            'target_user_id' => $targetUser->id,
            'actor_id' => $admin->id,
        ]);

        // Cek log perubahan role
        $this->assertDatabaseHas('user_logs', [
            'action' => 'role_changed',
            'target_user_id' => $targetUser->id,
            'actor_id' => $admin->id,
        ]);
    }

    /**
     * AC 6.1 & 6.2: Sistem mencatat aktivasi dan nonaktivasi akun beserta alasan opsional.
     */
    public function test_account_activation_and_deactivation_are_logged_with_reason(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $targetUser = User::factory()->create(['role' => 'warga', 'is_active' => true]);

        // 1. Nonaktifkan akun dengan alasan
        $response = $this->actingAs($admin)->patch(route('admin.users.toggle-active', $targetUser->id), [
            'reason' => 'Pelanggaran ketentuan pelaporan berulang kali',
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $targetUser->refresh();
        $this->assertFalse($targetUser->is_active);
        $this->assertNotNull($targetUser->deactivated_at);

        $this->assertDatabaseHas('user_logs', [
            'action' => 'account_deactivated',
            'target_user_id' => $targetUser->id,
            'actor_id' => $admin->id,
            'reason' => 'Pelanggaran ketentuan pelaporan berulang kali',
        ]);

        // 2. Aktifkan kembali akun
        $response = $this->actingAs($admin)->patch(route('admin.users.toggle-active', $targetUser->id), [
            'reason' => 'Masa penangguhan telah selesai',
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $targetUser->refresh();
        $this->assertTrue($targetUser->is_active);
        $this->assertNull($targetUser->deactivated_at);

        $this->assertDatabaseHas('user_logs', [
            'action' => 'account_activated',
            'target_user_id' => $targetUser->id,
            'actor_id' => $admin->id,
            'reason' => 'Masa penangguhan telah selesai',
        ]);
    }

    /**
     * AC 6.1: Akun nonaktif tidak dapat login dan menerima pesan yang jelas.
     */
    public function test_deactivated_user_cannot_login(): void
    {
        $user = User::factory()->create([
            'email' => 'nonaktif@example.com',
            'password' => Hash::make('password123'),
            'is_active' => false,
        ]);

        $response = $this->post(route('login'), [
            'email' => 'nonaktif@example.com',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();

        // Pesan jelas tentang akun nonaktif
        $this->assertEquals(
            'Akun Anda sedang dinonaktifkan oleh administrator.',
            session('errors')->get('email')[0]
        );
    }

    /**
     * AC 6.3: Sistem mencatat log event login berhasil dan login gagal.
     */
    public function test_login_success_and_failure_are_logged(): void
    {
        $user = User::factory()->create([
            'email' => 'aktif@example.com',
            'password' => Hash::make('password123'),
            'is_active' => true,
        ]);

        // 1. Percobaan login gagal (password salah)
        $this->post(route('login'), [
            'email' => 'aktif@example.com',
            'password' => 'passwordsalah',
        ]);

        $this->assertDatabaseHas('user_logs', [
            'action' => 'login_failed',
            'target_user_id' => $user->id,
        ]);

        // 2. Percobaan login sukses
        $this->post(route('login'), [
            'email' => 'aktif@example.com',
            'password' => 'password123',
        ]);

        $this->assertAuthenticatedAs($user);

        $this->assertDatabaseHas('user_logs', [
            'action' => 'login_success',
            'target_user_id' => $user->id,
            'actor_id' => $user->id,
        ]);
    }

    /**
     * AC 6.4 & 6.6: Admin dapat melihat daftar log dengan filter jenis aksi, rentang tanggal, dan search.
     */
    public function test_admin_can_view_and_filter_user_logs(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $targetUser = User::factory()->create(['name' => 'Ahmad Subarjo', 'email' => 'ahmad@example.com']);

        // Buat log
        $logService = app(UserLogService::class);
        $logService->log('role_changed', $targetUser->id, ['role' => 'warga'], ['role' => 'petugas'], 'Promosi', $admin->id);
        $logService->log('account_activated', $targetUser->id, null, null, null, $admin->id);

        $response = $this->actingAs($admin)->get(route('admin.user-logs.index', [
            'action' => 'role_changed',
            'search' => 'Ahmad',
        ]));

        $response->assertOk();
        $response->assertViewIs('admin.user-logs.index');
        $response->assertSee('Perubahan Role');
        $response->assertSee('Ahmad Subarjo');
    }

    /**
     * AC 6.5: Log bersifat append-only, tidak ada endpoint ubah atau hapus log.
     */
    public function test_user_logs_have_no_update_or_delete_routes(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

        // Route DELETE dan PUT/PATCH pada user-logs tidak didefinisikan (404/405)
        $responseDelete = $this->actingAs($admin)->delete('/admin/user-logs/1');
        $this->assertTrue(in_array($responseDelete->status(), [404, 405]));

        $responsePut = $this->actingAs($admin)->put('/admin/user-logs/1', []);
        $this->assertTrue(in_array($responsePut->status(), [404, 405]));
    }

    /**
     * AC 6.6: Password plaintext maupun hash tidak pernah disimpan di dalam database log.
     */
    public function test_password_plain_and_hash_are_never_in_logs(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $logService = app(UserLogService::class);

        $log = $logService->log(
            'password_changed',
            $admin->id,
            ['password' => 'secretPlainPassword123', 'remember_token' => 'token123'],
            ['password' => bcrypt('newSecretPassword123'), 'password_confirmation' => 'newSecretPassword123']
        );

        $rawLog = UserLog::find($log->id);
        $jsonString = json_encode($rawLog->toArray());

        $this->assertStringNotContainsString('secretPlainPassword123', $jsonString);
        $this->assertStringNotContainsString('newSecretPassword123', $jsonString);
        $this->assertArrayNotHasKey('password', $rawLog->old_value ?? []);
        $this->assertArrayNotHasKey('password', $rawLog->new_value ?? []);
    }

    /**
     * AC 4.3 & PBI-6: Detail user menampilkan riwayat log pengguna terkait.
     */
    public function test_user_detail_displays_user_activity_logs(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $targetUser = User::factory()->create(['name' => 'Target Log', 'role' => 'petugas']);

        $logService = app(UserLogService::class);
        $logService->log('role_changed', $targetUser->id, ['role' => 'warga'], ['role' => 'petugas'], 'Ditugaskan', $admin->id);

        $response = $this->actingAs($admin)->get(route('admin.users.show', $targetUser->id));

        $response->assertOk();
        $response->assertSee('Riwayat Log Aktivitas Pengguna');
        $response->assertSee('Perubahan Role');
    }
}

