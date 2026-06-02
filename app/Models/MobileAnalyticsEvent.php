<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'device_id',
    'session_id',
    'event_name',
    'screen_id',
    'action',
    'platform',
    'app_version',
    'occurred_at',
    'ip_address',
    'user_agent',
    'metadata',
])]
class MobileAnalyticsEvent extends Model
{
    public const EVENT_OPTIONS = [
        'app_open' => 'App open',
        'heartbeat' => 'Heartbeat',
        'screen_view' => 'Screen view',
        'action' => 'Action',
        'config_update' => 'Config update',
        'error' => 'Error',
    ];

    public static function eventLabel(?string $eventName): string
    {
        if (blank($eventName)) {
            return '-';
        }

        return self::EVENT_OPTIONS[$eventName] ?? str((string) $eventName)->headline()->toString();
    }

    public static function eventColor(?string $eventName): string
    {
        return match ($eventName) {
            'app_open', 'heartbeat' => 'success',
            'screen_view' => 'info',
            'action', 'config_update' => 'primary',
            'error' => 'danger',
            default => 'gray',
        };
    }

    protected function casts(): array
    {
        return [
            'occurred_at' => 'datetime',
            'metadata' => 'array',
        ];
    }
}
