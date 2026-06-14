<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CustomerProfile;
use App\Models\User;
use App\Support\Customers\CustomerProfileUpdater;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;

class AuthController extends Controller
{
    public function register(Request $request, CustomerProfileUpdater $profiles): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'phone' => ['nullable', 'string', 'max:255'],
            'delivery_address' => ['nullable', 'string', 'max:2000'],
            'device_name' => ['nullable', 'string', 'max:255'],
        ]);

        $this->ensureCustomerRoles();

        $user = User::query()->create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
        ]);

        $user->assignRole('customer');

        $profile = $profiles->update($user, [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'delivery_address' => $validated['delivery_address'] ?? null,
        ]);

        $token = $user->createToken($validated['device_name'] ?? 'android')->plainTextToken;

        return response()->json([
            'data' => [
                'token' => $token,
                'user' => $this->userPayload($user, $profile),
            ],
        ], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'device_name' => ['nullable', 'string', 'max:255'],
        ]);

        $user = User::query()->where('email', $validated['email'])->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $token = $user->createToken($validated['device_name'] ?? 'android')->plainTextToken;

        return response()->json([
            'data' => [
                'token' => $token,
                'user' => $this->userPayload($user),
            ],
        ]);
    }

    public function user(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'data' => $this->userPayload($user),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->json([
            'data' => [
                'revoked' => true,
            ],
        ]);
    }

    private function ensureCustomerRoles(): void
    {
        Role::query()->firstOrCreate([
            'name' => 'admin',
            'guard_name' => 'web',
        ]);

        Role::query()->firstOrCreate([
            'name' => 'customer',
            'guard_name' => 'web',
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function userPayload(User $user, ?CustomerProfile $profile = null): array
    {
        $profile ??= CustomerProfile::query()
            ->where('user_id', $user->id)
            ->first();

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'profile' => $profile ? [
                'name' => $profile->name,
                'phone' => $profile->phone,
                'email' => $profile->email,
                'delivery_address' => $profile->delivery_address,
            ] : null,
        ];
    }
}
