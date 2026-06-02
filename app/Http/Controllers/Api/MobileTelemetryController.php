<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MobileAnalyticsEvent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;

class MobileTelemetryController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'device_id' => ['required', 'string', 'max:128'],
            'session_id' => ['nullable', 'string', 'max:128'],
            'platform' => ['nullable', 'string', 'max:32'],
            'app_version' => ['nullable', 'string', 'max:32'],
            'event_name' => ['required_without:events', 'string', 'max:64'],
            'screen_id' => ['nullable', 'string', 'max:64'],
            'action' => ['nullable', 'string', 'max:128'],
            'occurred_at' => ['nullable', 'date'],
            'metadata' => ['nullable', 'array'],
            'events' => ['nullable', 'array', 'max:50'],
            'events.*.event_name' => ['required_with:events', 'string', 'max:64'],
            'events.*.screen_id' => ['nullable', 'string', 'max:64'],
            'events.*.action' => ['nullable', 'string', 'max:128'],
            'events.*.occurred_at' => ['nullable', 'date'],
            'events.*.metadata' => ['nullable', 'array'],
        ]);

        $events = $validated['events'] ?? [[
            'event_name' => $validated['event_name'] ?? null,
            'screen_id' => $validated['screen_id'] ?? null,
            'action' => $validated['action'] ?? null,
            'occurred_at' => $validated['occurred_at'] ?? null,
            'metadata' => $validated['metadata'] ?? null,
        ]];

        if ($events === []) {
            return response()->json([
                'message' => 'At least one telemetry event is required.',
                'errors' => [
                    'events' => ['At least one telemetry event is required.'],
                ],
            ], 422);
        }

        $now = now();
        $records = collect($events)
            ->map(fn (array $event): array => [
                'device_id' => $validated['device_id'],
                'session_id' => $validated['session_id'] ?? null,
                'event_name' => $event['event_name'],
                'screen_id' => $event['screen_id'] ?? null,
                'action' => $event['action'] ?? null,
                'platform' => $validated['platform'] ?? null,
                'app_version' => $validated['app_version'] ?? null,
                'occurred_at' => $this->occurredAt($event['occurred_at'] ?? null),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'metadata' => Arr::has($event, 'metadata') && $event['metadata'] !== null
                    ? json_encode($event['metadata'], JSON_THROW_ON_ERROR)
                    : null,
                'created_at' => $now,
                'updated_at' => $now,
            ])
            ->all();

        MobileAnalyticsEvent::query()->insert($records);

        return response()->json([
            'data' => [
                'accepted' => count($records),
            ],
        ], 201);
    }

    private function occurredAt(?string $value): Carbon
    {
        if (blank($value)) {
            return now();
        }

        return Carbon::parse($value);
    }
}
