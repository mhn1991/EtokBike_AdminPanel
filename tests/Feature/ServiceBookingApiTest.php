<?php

namespace Tests\Feature;

use App\Models\CustomerProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceBookingApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_anonymous_request_cannot_overwrite_a_registered_users_profile(): void
    {
        $victim = User::factory()->create([
            'name' => 'Victim',
            'email' => 'victim@example.com',
        ]);

        $victimProfile = CustomerProfile::query()->create([
            'user_id' => $victim->id,
            'name' => 'Victim',
            'email' => 'victim@example.com',
            'phone' => '+989120000000',
            'delivery_address' => 'Victim real address',
            'is_active' => true,
        ]);

        $this->postJson('/api/service-bookings', [
            'customer_name' => 'Attacker',
            'customer_email' => 'victim@example.com',
            'customer_phone' => '+989129999999',
            'service_type' => 'تنظیم دنده و ترمز',
        ])->assertCreated();

        $this->assertDatabaseHas('customer_profiles', [
            'id' => $victimProfile->id,
            'phone' => '+989120000000',
            'delivery_address' => 'Victim real address',
        ]);
    }

    public function test_it_creates_a_service_booking_from_the_api(): void
    {
        $response = $this->postJson('/api/service-bookings', [
            'customer_name' => 'Mobile Customer',
            'customer_phone' => '+989120000000',
            'customer_email' => 'mobile@example.com',
            'service_type' => 'تنظیم دنده و ترمز',
            'bike_label' => 'ETX 200',
            'preferred_time' => 'فردا ۱۰:۳۰',
            'problem_description' => 'صدای زنجیر هنگام تعویض دنده',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('data.status', 'pending')
            ->assertJsonPath('data.service_type', 'تنظیم دنده و ترمز')
            ->assertJsonPath('data.bike_label', 'ETX 200');

        $this->assertDatabaseHas('service_bookings', [
            'customer_name' => 'Mobile Customer',
            'service_type' => 'تنظیم دنده و ترمز',
            'status' => 'pending',
        ]);
    }
}
