<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceBookingApiTest extends TestCase
{
    use RefreshDatabase;

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
