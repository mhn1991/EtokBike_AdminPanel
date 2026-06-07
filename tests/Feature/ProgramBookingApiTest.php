<?php

namespace Tests\Feature;

use App\Models\Program;
use App\Models\ProgramCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProgramBookingApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_a_program_booking_from_the_api(): void
    {
        $category = ProgramCategory::query()->create([
            'slug' => 'rides',
            'label' => 'Rides',
            'title' => 'Ride programs',
        ]);

        $program = Program::query()->create([
            'program_category_id' => $category->id,
            'slug' => 'ride-test',
            'title' => 'Ride Test',
            'subtitle' => 'Morning ride',
            'date_value' => '2026-06-20',
            'date_label' => 'Saturday morning',
            'program_state' => 'future',
            'capacity' => 4,
        ]);

        $response = $this->postJson('/api/program-bookings', [
            'program' => 'ride-test',
            'customer_name' => 'Mobile Customer',
            'customer_phone' => '+989120000000',
            'attendees' => 2,
            'customer_notes' => 'Need helmet sizing help.',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('data.program', 'ride-test')
            ->assertJsonPath('data.customer_name', 'Mobile Customer')
            ->assertJsonPath('data.attendees', 2)
            ->assertJsonPath('data.status', 'pending');

        $this->assertDatabaseHas('program_bookings', [
            'program_id' => $program->id,
            'customer_name' => 'Mobile Customer',
            'attendees' => 2,
            'status' => 'pending',
        ]);

        $this->assertSame(2, $program->fresh()->reserved_count);
    }

    public function test_it_rejects_program_bookings_without_capacity(): void
    {
        $category = ProgramCategory::query()->create([
            'slug' => 'rides',
            'label' => 'Rides',
            'title' => 'Ride programs',
        ]);

        Program::query()->create([
            'program_category_id' => $category->id,
            'slug' => 'full-ride',
            'title' => 'Full Ride',
            'subtitle' => 'Morning ride',
            'date_value' => '2026-06-20',
            'date_label' => 'Saturday morning',
            'program_state' => 'future',
            'capacity' => 1,
            'reserved_count' => 1,
        ]);

        $this->postJson('/api/program-bookings', [
            'program' => 'full-ride',
            'customer_name' => 'Mobile Customer',
            'attendees' => 1,
        ])->assertUnprocessable();
    }
}
