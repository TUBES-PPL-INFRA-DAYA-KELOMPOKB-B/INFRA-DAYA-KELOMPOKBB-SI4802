<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RateLimitingSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_endpoint_is_rate_limited(): void
    {
        // Route has throttle:10,1
        for ($i = 0; $i < 10; $i++) {
            $this->post('/register', [
                'name' => 'User Test',
                'email' => "user{$i}@test.com",
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ]);
            auth()->logout();
        }

        // 11th attempt must be throttled with HTTP 429
        $response = $this->post('/register', [
            'name' => 'User Test Overflow',
            'email' => 'useroverflow@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(429);
    }

    public function test_lapor_endpoint_is_rate_limited(): void
    {
        $warga = User::factory()->create(['role' => 'warga']);

        for ($i = 0; $i < 10; $i++) {
            $this->actingAs($warga)->post(route('warga.laporan.store'), [
                'judul' => 'Tes Lapor',
            ]);
        }

        // 11th attempt must be throttled with HTTP 429
        $response = $this->actingAs($warga)->post(route('warga.laporan.store'), [
            'judul' => 'Tes Lapor Overflow',
        ]);

        $response->assertStatus(429);
    }
}
