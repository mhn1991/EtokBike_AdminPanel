<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MessageDepartment;
use App\Support\Api\OptionalSanctumUser;
use App\Support\Customers\CustomerProfileUpdater;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerMessageController extends Controller
{
    public function store(Request $request, CustomerProfileUpdater $profiles): JsonResponse
    {
        $validated = $request->validate([
            'department' => ['nullable', 'string', 'max:255'],
            'message_department_id' => ['nullable', 'integer'],
            'customer_name' => ['nullable', 'string', 'max:255'],
            'customer_phone' => ['nullable', 'string', 'max:255'],
            'customer_email' => ['nullable', 'email', 'max:255'],
            'label' => ['nullable', 'string', 'max:255'],
            'text' => ['required', 'string'],
        ]);

        $department = $this->findDepartment($validated);

        if (! $department) {
            return response()->json([
                'message' => 'The selected message department is invalid.',
                'errors' => [
                    'department' => ['The selected message department is invalid.'],
                ],
            ], 422);
        }

        $user = OptionalSanctumUser::resolve($request);
        $profile = $profiles->update($user, $validated);

        $message = $department->messages()->create([
            'user_id' => $user?->id,
            'sender' => 'client',
            'label' => $validated['label'] ?? $profile?->name ?? $user?->name ?? 'مشتری',
            'text' => $validated['text'],
            'time_label' => 'اکنون',
            'is_unread' => true,
        ]);

        return response()->json([
            'data' => [
                'id' => $message->id,
                'department' => $department->slug,
                'sender' => $message->sender,
                'label' => $message->label,
                'text' => $message->text,
                'time' => $message->time_label,
                'is_unread' => $message->is_unread,
            ],
        ], 201);
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    private function findDepartment(array $validated): ?MessageDepartment
    {
        if (! empty($validated['message_department_id'])) {
            return MessageDepartment::query()
                ->where('is_active', true)
                ->find($validated['message_department_id']);
        }

        if (! empty($validated['department'])) {
            return MessageDepartment::query()
                ->where('is_active', true)
                ->where('slug', $validated['department'])
                ->first();
        }

        return null;
    }
}
