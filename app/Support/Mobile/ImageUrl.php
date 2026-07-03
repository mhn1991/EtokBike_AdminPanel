<?php

namespace App\Support\Mobile;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageUrl
{
    public static function resolve(?string $value): ?string
    {
        return static::resolveWithBaseUrl($value);
    }

    public static function resolveForMobile(?string $value): ?string
    {
        $baseUrl = config('mobile.base_url');

        return static::resolveWithBaseUrl($value, filled($baseUrl) ? (string) $baseUrl : null);
    }

    private static function resolveWithBaseUrl(?string $value, ?string $baseUrl = null): ?string
    {
        if (blank($value)) {
            return null;
        }

        $value = trim((string) $value);

        if (Str::startsWith($value, ['http://', 'https://'])) {
            return $value;
        }

        if (filled($baseUrl)) {
            return rtrim((string) $baseUrl, '/').'/storage/'.ltrim($value, '/');
        }

        $assetUrl = config('app.asset_url');

        if (filled($assetUrl)) {
            return rtrim((string) $assetUrl, '/').'/storage/'.ltrim($value, '/');
        }

        return Storage::disk('public')->url($value);
    }
}
