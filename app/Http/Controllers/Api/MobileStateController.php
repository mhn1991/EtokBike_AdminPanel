<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CustomerMessage;
use App\Models\MobileCartItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MobileStateController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'device_id' => ['required', 'string', 'max:255'],
        ]);

        return response()->json([
            'data' => [
                'cart_count' => MobileCartItem::query()
                    ->where('device_id', $validated['device_id'])
                    ->sum('quantity'),
                'unread_message_count' => CustomerMessage::query()
                    ->where('is_unread', true)
                    ->count(),
            ],
        ]);
    }
}
