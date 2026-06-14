<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MobileCartItem;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_email' => ['nullable', 'email', 'max:255'],
            'customer_phone' => ['nullable', 'string', 'max:255'],
            'fulfillment_method' => ['nullable', 'string', 'in:pickup,delivery'],
            'customer_notes' => ['nullable', 'string'],
            'device_id' => ['nullable', 'string', 'max:255'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['nullable', 'string', 'max:255'],
            'items.*.title' => ['required', 'string', 'max:255'],
            'items.*.sku' => ['nullable', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['required', 'integer', 'min:0'],
            'items.*.metadata' => ['nullable', 'array'],
        ]);

        foreach ($validated['items'] as $item) {
            $product = $this->findProductForItem($item);

            if ($product && ! $product->hasEnoughStock((int) $item['quantity'])) {
                return response()->json([
                    'message' => "Not enough stock for {$product->title}.",
                    'errors' => [
                        'items' => ["Not enough stock for {$product->title}."],
                    ],
                ], 422);
            }
        }

        $order = DB::transaction(function () use ($validated): Order {
            $order = Order::query()->create([
                'customer_name' => $validated['customer_name'],
                'customer_email' => $validated['customer_email'] ?? null,
                'customer_phone' => $validated['customer_phone'] ?? null,
                'fulfillment_method' => $validated['fulfillment_method'] ?? 'pickup',
                'customer_notes' => $validated['customer_notes'] ?? null,
                'status' => 'pending',
                'payment_status' => 'unpaid',
            ]);

            foreach ($validated['items'] as $item) {
                $order->items()->create($item);
            }

            if (! empty($validated['device_id'])) {
                MobileCartItem::query()
                    ->where('device_id', $validated['device_id'])
                    ->delete();
            }

            return $order->fresh(['items']);
        });

        return response()->json([
            'data' => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'status' => $order->status,
                'payment_status' => $order->payment_status,
                'subtotal' => $order->subtotal,
                'total' => $order->total,
                'items' => $order->items,
            ],
        ], 201);
    }

    /**
     * @param  array<string, mixed>  $item
     */
    private function findProductForItem(array $item): ?Product
    {
        $metadataProductId = $item['metadata']['product_database_id'] ?? null;

        if ($metadataProductId) {
            return Product::query()->find($metadataProductId);
        }

        if (! empty($item['product_id'])) {
            return Product::query()
                ->where('slug', $item['product_id'])
                ->orWhere('sku', $item['product_id'])
                ->first();
        }

        if (! empty($item['sku'])) {
            return Product::query()
                ->where('sku', $item['sku'])
                ->orWhere('slug', $item['sku'])
                ->first();
        }

        return null;
    }
}
