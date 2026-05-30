<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ServiceBooking;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ServiceBookingController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_phone' => ['nullable', 'string', 'max:255'],
            'customer_email' => ['nullable', 'email', 'max:255'],
            'service_type' => ['required', 'string', 'max:255'],
            'bike_label' => ['nullable', 'string', 'max:255'],
            'preferred_time' => ['nullable', 'string', 'max:255'],
            'problem_description' => ['nullable', 'string'],
        ]);

        $booking = ServiceBooking::query()->create([
            ...$validated,
            'status' => 'pending',
        ]);

        return response()->json([
            'data' => [
                'id' => $booking->id,
                'customer_name' => $booking->customer_name,
                'service_type' => $booking->service_type,
                'bike_label' => $booking->bike_label,
                'preferred_time' => $booking->preferred_time,
                'status' => $booking->status,
            ],
        ], 201);
    }
}
