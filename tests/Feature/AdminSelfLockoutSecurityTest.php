<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminSelfLockoutSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_cannot_deactivate_self_via_toggle_active(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->patch(route('admin.users.toggle-active', $admin->id));

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('error');

        $admin->refresh();
        $this->assertTrue($admin->is_active);
    }

    public function test_admin_can_toggle_active_for_other_users(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $petugas = User::factory()->create([
            'role' => 'petugas',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->patch(route('admin.users.toggle-active', $petugas->id));

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('success');

        $petugas->refresh();
        $this->assertFalse($petugas->is_active);
    }
}
