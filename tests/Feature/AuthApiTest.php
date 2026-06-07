<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_logs_in_returns_the_user_and_logs_out(): void
    {
        User::factory()->create([
            'name' => 'Mobile User',
            'email' => 'mobile@example.com',
            'password' => 'password',
        ]);

        $login = $this->postJson('/api/auth/login', [
            'email' => 'mobile@example.com',
            'password' => 'password',
            'device_name' => 'android',
        ]);

        $login
            ->assertOk()
            ->assertJsonPath('data.user.email', 'mobile@example.com');

        $token = $login->json('data.token');

        $this->withToken($token)
            ->getJson('/api/auth/user')
            ->assertOk()
            ->assertJsonPath('data.name', 'Mobile User');

        $this->withToken($token)
            ->postJson('/api/auth/logout')
            ->assertOk()
            ->assertJsonPath('data.revoked', true);
    }
}
