<?php

namespace App\Support\Mobile;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageUrl
{
    public static function resolve(?string $value): ?string
    {
        if (blank($value)) {
            return null;
        }

        $value = (string) $value;

        if (Str::startsWith($value, ['http://', 'https://'])) {
            return $value;
        }

        return Storage::disk('public')->url($value);
    }
}
