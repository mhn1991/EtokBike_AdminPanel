<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MobileCartItem;
use App\Models\Product;
use App\Models\User;
use App\Support\Api\OptionalSanctumUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MobileCartController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'device_id' => ['required', 'string', 'max:255'],
        ]);

        $user = OptionalSanctumUser::resolve($request);

        return response()->json([
            'data' => $this->cartPayload($validated['device_id'], $user),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'device_id' => ['required', 'string', 'max:255'],
            'product_id' => ['nullable', 'integer'],
            'product' => ['nullable', 'string', 'max:255'],
            'quantity' => ['nullable', 'integer', 'min:1'],
        ]);

        $product = $this->findProduct($validated);
        $user = OptionalSanctumUser::resolve($request);

        if (! $product) {
            return response()->json([
                'message' => 'The selected product is invalid.',
                'errors' => [
                    'product' => ['The selected product is invalid.'],
                ],
            ], 422);
        }

        $quantity = (int) ($validated['quantity'] ?? 1);
        $existingQuantity = (int) $this->cartQuery($validated['device_id'], $user)
            ->where('product_id', $product->id)
            ->value('quantity');

        if (! $product->hasEnoughStock($existingQuantity + $quantity)) {
            return response()->json([
                'message' => 'Not enough stock for this product.',
                'errors' => [
                    'quantity' => ['Not enough stock for this product.'],
                ],
            ], 422);
        }

        $item = MobileCartItem::query()->firstOrNew([
            'device_id' => $this->storedDeviceId($validated['device_id'], $user),
            'product_id' => $product->id,
        ]);

        $item->user_id = $user?->id;
        $item->quantity = max(1, ($item->exists ? $item->quantity : 0) + $quantity);
        $item->save();

        return response()->json([
            'data' => $this->cartPayload($validated['device_id'], $user),
        ], 201);
    }

    public function update(Request $request, MobileCartItem $item): JsonResponse
    {
        $validated = $request->validate([
            'device_id' => ['required', 'string', 'max:255'],
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $user = OptionalSanctumUser::resolve($request);

        abort_unless($this->ownsCartItem($item, $validated['device_id'], $user), 404);

        if ($item->product && ! $item->product->hasEnoughStock((int) $validated['quantity'])) {
            return response()->json([
                'message' => 'Not enough stock for this product.',
                'errors' => [
                    'quantity' => ['Not enough stock for this product.'],
                ],
            ], 422);
        }

        $item->update([
            'quantity' => $validated['quantity'],
        ]);

        return response()->json([
            'data' => $this->cartPayload($validated['device_id'], $user),
        ]);
    }

    public function destroy(Request $request, MobileCartItem $item): JsonResponse
    {
        $validated = $request->validate([
            'device_id' => ['required', 'string', 'max:255'],
        ]);

        $user = OptionalSanctumUser::resolve($request);

        abort_unless($this->ownsCartItem($item, $validated['device_id'], $user), 404);

        $item->delete();

        return response()->json([
            'data' => $this->cartPayload($validated['device_id'], $user),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function cartPayload(string $deviceId, ?User $user): array
    {
        $items = $this->cartQuery($deviceId, $user)
            ->with('product.category')
            ->latest()
            ->get();

        $subtotal = $items->sum(fn (MobileCartItem $item): int => $item->quantity * ($item->product?->price_value ?? 0));

        return [
            'items' => $items
                ->map(fn (MobileCartItem $item): array => [
                    'id' => $item->id,
                    'quantity' => $item->quantity,
                    'line_total' => $item->quantity * ($item->product?->price_value ?? 0),
                    'product' => $item->product?->toMobilePayload(),
                ])
                ->values()
                ->all(),
            'count' => $items->sum('quantity'),
            'subtotal' => $subtotal,
            'total' => $subtotal,
        ];
    }

    private function cartQuery(string $deviceId, ?User $user)
    {
        return MobileCartItem::query()
            ->when(
                $user,
                fn ($query) => $query->where('user_id', $user->id),
                fn ($query) => $query->where('device_id', $deviceId),
            );
    }

    private function storedDeviceId(string $deviceId, ?User $user): string
    {
        return $user ? 'user:'.$user->id : $deviceId;
    }

    private function ownsCartItem(MobileCartItem $item, string $deviceId, ?User $user): bool
    {
        if ($user) {
            return (int) $item->user_id === (int) $user->id;
        }

        return $item->device_id === $deviceId;
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    private function findProduct(array $validated): ?Product
    {
        if (! empty($validated['product_id'])) {
            return Product::query()
                ->where('is_active', true)
                ->find($validated['product_id']);
        }

        if (! empty($validated['product'])) {
            return Product::query()
                ->where('is_active', true)
                ->where('slug', $validated['product'])
                ->first();
        }

        return null;
    }
}
