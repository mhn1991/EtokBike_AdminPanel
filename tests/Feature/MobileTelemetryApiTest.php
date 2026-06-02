<?php

namespace Tests\Feature;

use App\Models\MobileAnalyticsEvent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MobileTelemetryApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_stores_batched_mobile_telemetry_events(): void
    {
        $response = $this->postJson('/api/mobile/telemetry', [
            'device_id' => 'device-123',
            'session_id' => 'session-abc',
            'platform' => 'android',
            'app_version' => '10',
            'events' => [
                [
                    'event_name' => 'app_open',
                    'screen_id' => 'home',
                    'occurred_at' => now()->toIso8601String(),
                    'metadata' => [
                        'model' => 'Pixel 8',
                    ],
                ],
                [
                    'event_name' => 'screen_view',
                    'screen_id' => 'shop',
                    'action' => 'bottom_navigation',
                ],
            ],
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('data.accepted', 2);

        $this->assertDatabaseCount('mobile_analytics_events', 2);
        $this->assertDatabaseHas('mobile_analytics_events', [
            'device_id' => 'device-123',
            'session_id' => 'session-abc',
            'event_name' => 'screen_view',
            'screen_id' => 'shop',
            'action' => 'bottom_navigation',
            'platform' => 'android',
            'app_version' => '10',
        ]);

        $this->assertSame(
            ['model' => 'Pixel 8'],
            MobileAnalyticsEvent::query()->where('event_name', 'app_open')->firstOrFail()->metadata,
        );
    }

    public function test_it_stores_a_single_mobile_telemetry_event(): void
    {
        $response = $this->postJson('/api/mobile/telemetry', [
            'device_id' => 'device-456',
            'event_name' => 'heartbeat',
            'screen_id' => 'services',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('data.accepted', 1);

        $this->assertDatabaseHas('mobile_analytics_events', [
            'device_id' => 'device-456',
            'event_name' => 'heartbeat',
            'screen_id' => 'services',
        ]);
    }

    public function test_it_rejects_empty_mobile_telemetry_payloads(): void
    {
        $this->postJson('/api/mobile/telemetry', [
            'device_id' => 'device-empty',
            'events' => [],
        ])->assertUnprocessable();
    }
}
