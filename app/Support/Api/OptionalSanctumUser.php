<?php

namespace App\Support\Api;

use App\Models\User;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;

class OptionalSanctumUser
{
    public static function resolve(Request $request): ?User
    {
        $token = $request->bearerToken();

        if (blank($token)) {
            return null;
        }

        $accessToken = PersonalAccessToken::findToken($token);
        $user = $accessToken?->tokenable;

        return $user instanceof User ? $user : null;
    }
}
