<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CustomerMessage;
use App\Models\MobileCartItem;
use App\Support\Api\OptionalSanctumUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MobileStateController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'device_id' => ['required', 'string', 'max:255'],
        ]);

        $user = OptionalSanctumUser::resolve($request);

        return response()->json([
            'data' => [
                'cart_count' => MobileCartItem::query()
                    ->when(
                        $user,
                        fn ($query) => $query->where('user_id', $user->id),
                        fn ($query) => $query->where('device_id', $validated['device_id']),
                    )
                    ->sum('quantity'),
                'unread_message_count' => CustomerMessage::query()
                    ->where('is_unread', true)
                    ->when($user, fn ($query) => $query->where('user_id', $user->id))
                    ->count(),
            ],
        ]);
    }
}
