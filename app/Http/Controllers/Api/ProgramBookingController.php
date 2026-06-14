<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Program;
use App\Models\ProgramBooking;
use App\Support\Api\OptionalSanctumUser;
use App\Support\Customers\CustomerProfileUpdater;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProgramBookingController extends Controller
{
    public function store(Request $request, CustomerProfileUpdater $profiles): JsonResponse
    {
        $validated = $request->validate([
            'program_id' => ['nullable', 'integer'],
            'program' => ['nullable', 'string', 'max:255'],
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_phone' => ['nullable', 'string', 'max:255'],
            'customer_email' => ['nullable', 'email', 'max:255'],
            'attendees' => ['nullable', 'integer', 'min:1'],
            'customer_notes' => ['nullable', 'string'],
        ]);

        $program = $this->findProgram($validated);

        if (! $program) {
            return response()->json([
                'message' => 'The selected program is invalid.',
                'errors' => [
                    'program' => ['The selected program is invalid.'],
                ],
            ], 422);
        }

        if ($program->program_state !== 'future') {
            throw ValidationException::withMessages([
                'program' => ['Only future programs can be booked.'],
            ]);
        }

        $attendees = (int) ($validated['attendees'] ?? 1);
        $user = OptionalSanctumUser::resolve($request);
        $profiles->update($user, $validated);

        $booking = DB::transaction(function () use ($program, $validated, $attendees, $user): ProgramBooking {
            /** @var Program $program */
            $program = Program::query()->lockForUpdate()->findOrFail($program->id);

            if ($program->capacity !== null && ($program->reserved_count + $attendees) > $program->capacity) {
                throw ValidationException::withMessages([
                    'attendees' => ['Not enough capacity remains for this program.'],
                ]);
            }

            return $program->bookings()->create([
                'user_id' => $user?->id,
                'customer_name' => $validated['customer_name'],
                'customer_phone' => $validated['customer_phone'] ?? null,
                'customer_email' => $validated['customer_email'] ?? null,
                'attendees' => $attendees,
                'customer_notes' => $validated['customer_notes'] ?? null,
                'status' => 'pending',
            ])->fresh(['program']);
        });

        return response()->json([
            'data' => [
                'id' => $booking->id,
                'program' => $booking->program?->slug,
                'program_title' => $booking->program?->title,
                'customer_name' => $booking->customer_name,
                'attendees' => $booking->attendees,
                'status' => $booking->status,
            ],
        ], 201);
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    private function findProgram(array $validated): ?Program
    {
        if (! empty($validated['program_id'])) {
            return Program::query()
                ->where('is_active', true)
                ->find($validated['program_id']);
        }

        if (! empty($validated['program'])) {
            return Program::query()
                ->where('is_active', true)
                ->where('slug', $validated['program'])
                ->first();
        }

        return null;
    }
}
