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

    public function test_it_registers_customer_and_exposes_account_profile(): void
    {
        $register = $this->postJson('/api/auth/register', [
            'name' => 'Registered Customer',
            'email' => 'registered@example.com',
            'password' => 'password123',
            'phone' => '+989126666666',
            'delivery_address' => 'Customer address',
            'device_name' => 'android',
        ]);

        $register
            ->assertCreated()
            ->assertJsonPath('data.user.email', 'registered@example.com')
            ->assertJsonPath('data.user.profile.phone', '+989126666666');

        $token = $register->json('data.token');

        $this->withToken($token)
            ->patchJson('/api/account', [
                'name' => 'Updated Customer',
                'email' => 'registered@example.com',
                'phone' => '+989126666666',
                'delivery_address' => 'Updated address',
            ])
            ->assertOk()
            ->assertJsonPath('data.profile.name', 'Updated Customer')
            ->assertJsonPath('data.profile.delivery_address', 'Updated address');

        $this->withToken($token)
            ->getJson('/api/account')
            ->assertOk()
            ->assertJsonPath('data.profile.name', 'Updated Customer');

        $this->assertDatabaseHas('customer_profiles', [
            'email' => 'registered@example.com',
            'delivery_address' => 'Updated address',
        ]);
    }
}
