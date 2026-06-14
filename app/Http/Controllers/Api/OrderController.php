<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DeliveryZone;
use App\Models\DiscountCode;
use App\Models\MobileCartItem;
use App\Models\Order;
use App\Models\PaymentTransaction;
use App\Models\Product;
use App\Models\Shipment;
use App\Support\Api\OptionalSanctumUser;
use App\Support\Customers\CustomerProfileUpdater;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    public function store(Request $request, CustomerProfileUpdater $profiles): JsonResponse
    {
        $validated = $request->validate([
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_email' => ['nullable', 'email', 'max:255'],
            'customer_phone' => ['nullable', 'string', 'max:255'],
            'fulfillment_method' => ['nullable', 'string', 'in:pickup,delivery'],
            'delivery_zone_id' => ['nullable', 'integer', 'exists:delivery_zones,id'],
            'delivery_address' => ['nullable', 'string'],
            'discount_code' => ['nullable', 'string', 'max:255'],
            'payment_method' => ['nullable', 'string', 'in:pay_in_store,bank_transfer,cash_on_delivery'],
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

        $subtotal = collect($validated['items'])
            ->sum(fn (array $item): int => (int) $item['quantity'] * (int) $item['unit_price']);
        $deliveryZone = $this->deliveryZone($validated, $subtotal);
        $deliveryTotal = $deliveryZone?->fee ?? 0;
        $discount = $this->discount($validated['discount_code'] ?? null, $subtotal, $deliveryTotal);
        $user = OptionalSanctumUser::resolve($request);

        $order = DB::transaction(function () use ($validated, $profiles, $user, $deliveryZone, $deliveryTotal, $discount): Order {
            $profiles->update($user, $validated);

            $order = Order::query()->create([
                'user_id' => $user?->id,
                'customer_name' => $validated['customer_name'],
                'customer_email' => $validated['customer_email'] ?? null,
                'customer_phone' => $validated['customer_phone'] ?? null,
                'fulfillment_method' => $validated['fulfillment_method'] ?? 'pickup',
                'customer_notes' => $validated['customer_notes'] ?? null,
                'status' => 'pending',
                'payment_status' => 'unpaid',
                'discount_total' => $discount['amount'],
                'delivery_total' => $deliveryTotal,
            ]);

            foreach ($validated['items'] as $item) {
                $order->items()->create($item);
            }

            if (($validated['fulfillment_method'] ?? 'pickup') === 'delivery') {
                Shipment::query()->create([
                    'order_id' => $order->id,
                    'delivery_zone_id' => $deliveryZone?->id,
                    'status' => 'pending',
                    'shipping_cost' => $deliveryTotal,
                    'delivery_address' => $validated['delivery_address'] ?? null,
                ]);
            }

            PaymentTransaction::query()->create([
                'order_id' => $order->id,
                'provider' => $validated['payment_method'] ?? 'pay_in_store',
                'status' => 'pending',
                'amount' => $order->fresh()->total,
                'currency' => 'IRR',
                'attempted_at' => now(),
            ]);

            $discount['record']?->increment('used_count');

            if (! empty($validated['device_id'])) {
                $cartQuery = MobileCartItem::query();

                if ($user) {
                    $cartQuery->where('user_id', $user->id);
                } else {
                    $cartQuery->where('device_id', $validated['device_id']);
                }

                $cartQuery->delete();
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

    /**
     * @param  array<string, mixed>  $validated
     */
    private function deliveryZone(array $validated, int $subtotal): ?DeliveryZone
    {
        if (($validated['fulfillment_method'] ?? 'pickup') !== 'delivery') {
            return null;
        }

        if (blank($validated['delivery_address'] ?? null)) {
            throw ValidationException::withMessages([
                'delivery_address' => ['Delivery address is required for delivery orders.'],
            ]);
        }

        if (blank($validated['delivery_zone_id'] ?? null)) {
            return null;
        }

        $zone = DeliveryZone::query()
            ->where('is_active', true)
            ->findOrFail($validated['delivery_zone_id']);

        if ($subtotal < $zone->minimum_order_total) {
            throw ValidationException::withMessages([
                'delivery_zone_id' => ['Order subtotal is below the minimum for this delivery zone.'],
            ]);
        }

        return $zone;
    }

    /**
     * @return array{amount: int, record: ?DiscountCode}
     */
    private function discount(?string $code, int $subtotal, int $deliveryTotal): array
    {
        $code = trim((string) $code);

        if ($code === '') {
            return ['amount' => 0, 'record' => null];
        }

        $discount = DiscountCode::query()
            ->where('is_active', true)
            ->where('code', $code)
            ->first();

        if (! $discount) {
            throw ValidationException::withMessages([
                'discount_code' => ['The discount code is invalid.'],
            ]);
        }

        if ($discount->starts_at && $discount->starts_at->isFuture()) {
            throw ValidationException::withMessages([
                'discount_code' => ['The discount code is not active yet.'],
            ]);
        }

        if ($discount->ends_at && $discount->ends_at->isPast()) {
            throw ValidationException::withMessages([
                'discount_code' => ['The discount code has expired.'],
            ]);
        }

        if ($discount->usage_limit !== null && $discount->used_count >= $discount->usage_limit) {
            throw ValidationException::withMessages([
                'discount_code' => ['The discount code usage limit has been reached.'],
            ]);
        }

        if ($subtotal < $discount->minimum_order_total) {
            throw ValidationException::withMessages([
                'discount_code' => ['The order subtotal is too low for this discount code.'],
            ]);
        }

        $amount = match ($discount->type) {
            'percent' => min($subtotal, (int) floor($subtotal * ($discount->value / 100))),
            'free_delivery' => $deliveryTotal,
            default => min($subtotal, $discount->value),
        };

        return ['amount' => max(0, $amount), 'record' => $discount];
    }
}
