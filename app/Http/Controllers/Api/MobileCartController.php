<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MobileCartItem;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MobileCartController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'device_id' => ['required', 'string', 'max:255'],
        ]);

        return response()->json([
            'data' => $this->cartPayload($validated['device_id']),
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

        if (! $product) {
            return response()->json([
                'message' => 'The selected product is invalid.',
                'errors' => [
                    'product' => ['The selected product is invalid.'],
                ],
            ], 422);
        }

        $quantity = (int) ($validated['quantity'] ?? 1);
        $existingQuantity = (int) MobileCartItem::query()
            ->where('device_id', $validated['device_id'])
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
            'device_id' => $validated['device_id'],
            'product_id' => $product->id,
        ]);

        $item->quantity = max(1, ($item->exists ? $item->quantity : 0) + $quantity);
        $item->save();

        return response()->json([
            'data' => $this->cartPayload($validated['device_id']),
        ], 201);
    }

    public function update(Request $request, MobileCartItem $item): JsonResponse
    {
        $validated = $request->validate([
            'device_id' => ['required', 'string', 'max:255'],
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        abort_unless($item->device_id === $validated['device_id'], 404);

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
            'data' => $this->cartPayload($validated['device_id']),
        ]);
    }

    public function destroy(Request $request, MobileCartItem $item): JsonResponse
    {
        $validated = $request->validate([
            'device_id' => ['required', 'string', 'max:255'],
        ]);

        abort_unless($item->device_id === $validated['device_id'], 404);

        $item->delete();

        return response()->json([
            'data' => $this->cartPayload($validated['device_id']),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function cartPayload(string $deviceId): array
    {
        $items = MobileCartItem::query()
            ->where('device_id', $deviceId)
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
