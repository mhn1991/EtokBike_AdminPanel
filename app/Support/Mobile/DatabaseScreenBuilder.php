<?php

namespace App\Support\Mobile;

use App\Models\MobileScreen;
use Illuminate\Support\Facades\Schema;

class DatabaseScreenBuilder
{
    /**
     * @param  array<string, mixed>  $fallback
     * @return array<string, mixed>
     */
    public static function build(array $fallback): array
    {
        if (! static::canUseDatabase()) {
            return $fallback;
        }

        $screenId = $fallback['screenId'] ?? null;

        if (! is_string($screenId) || blank($screenId)) {
            return $fallback;
        }

        $screen = MobileScreen::query()
            ->where('screen_id', $screenId)
            ->where('is_active', true)
            ->with('sections')
            ->first();

        if (! $screen) {
            return $fallback;
        }

        return $screen->toMobilePayload($fallback);
    }

    /**
     * @param  array<string, mixed>  $fallback
     */
    public static function version(array $fallback): int
    {
        if (! static::canUseDatabase()) {
            return (int) ($fallback['version'] ?? 1);
        }

        $screenId = $fallback['screenId'] ?? null;

        if (! is_string($screenId) || blank($screenId)) {
            return (int) ($fallback['version'] ?? 1);
        }

        $screen = MobileScreen::query()
            ->where('screen_id', $screenId)
            ->where('is_active', true)
            ->with('sections')
            ->first();

        if (! $screen) {
            return (int) ($fallback['version'] ?? 1);
        }

        return max((int) ($fallback['version'] ?? 1), $screen->mobileVersion());
    }

    private static function canUseDatabase(): bool
    {
        return Schema::hasTable('mobile_screens')
            && Schema::hasTable('mobile_screen_sections');
    }
}
