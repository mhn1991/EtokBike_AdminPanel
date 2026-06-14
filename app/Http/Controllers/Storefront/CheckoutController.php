<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\DeliveryMethod;
use App\Models\DeliveryZone;
use App\Models\DiscountCode;
use App\Models\Order;
use App\Models\PaymentTransaction;
use App\Models\Shipment;
use App\Support\Customers\CustomerProfileUpdater;
use App\Support\Storefront\StorefrontCart;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CheckoutController extends Controller
{
    public function show(StorefrontCart $cart): View|RedirectResponse
    {
        if ($cart->isEmpty()) {
            return redirect()->route('storefront.shop');
        }

        return view('storefront.checkout.show', [
            'lines' => $cart->lines(),
            'subtotal' => $cart->subtotal(),
            'deliveryMethods' => DeliveryMethod::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('title')
                ->get(),
            'deliveryZones' => DeliveryZone::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get(),
            'meta' => [
                'title' => 'تکمیل سفارش | EtokBike',
                'description' => 'تکمیل سفارش فروشگاه EtokBike.',
                'canonical' => route('storefront.checkout.show'),
                'robots' => 'noindex,nofollow',
            ],
        ]);
    }

    public function store(Request $request, StorefrontCart $cart, CustomerProfileUpdater $profiles): RedirectResponse
    {
        if ($cart->isEmpty()) {
            return redirect()->route('storefront.shop');
        }

        $validated = $request->validate([
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_email' => ['nullable', 'email', 'max:255'],
            'customer_phone' => ['required', 'string', 'max:255'],
            'fulfillment_method' => ['required', 'string', 'in:pickup,delivery'],
            'delivery_zone_id' => ['nullable', 'integer', 'exists:delivery_zones,id'],
            'delivery_address' => ['nullable', 'string', 'max:2000'],
            'discount_code' => ['nullable', 'string', 'max:255'],
            'payment_method' => ['nullable', 'string', 'in:pay_in_store,bank_transfer,cash_on_delivery'],
            'customer_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $lines = $cart->lines();

        if ($lines->isEmpty()) {
            $cart->clear();

            return redirect()->route('storefront.shop');
        }

        foreach ($lines as $line) {
            if (! $line['product']->hasEnoughStock($line['quantity'])) {
                return redirect()
                    ->route('storefront.cart.show')
                    ->withErrors([
                        'stock' => 'برخی محصولات سبد خرید موجودی کافی ندارند.',
                    ]);
            }
        }

        $subtotal = (int) $lines->sum('line_total');
        $deliveryZone = $this->deliveryZone($validated, $subtotal);
        $deliveryTotal = $deliveryZone?->fee ?? 0;
        $discount = $this->discount($validated['discount_code'] ?? null, $subtotal, $deliveryTotal);

        $order = DB::transaction(function () use ($validated, $lines, $profiles, $deliveryZone, $deliveryTotal, $discount): Order {
            $profile = $profiles->update(null, $validated);

            $order = Order::query()->create([
                'user_id' => $profile?->user_id,
                'customer_name' => $validated['customer_name'],
                'customer_email' => $validated['customer_email'] ?? null,
                'customer_phone' => $validated['customer_phone'],
                'fulfillment_method' => $validated['fulfillment_method'],
                'customer_notes' => $validated['customer_notes'] ?? null,
                'status' => 'pending',
                'payment_status' => 'unpaid',
                'currency' => 'IRR',
                'discount_total' => $discount['amount'],
                'delivery_total' => $deliveryTotal,
            ]);

            foreach ($lines as $line) {
                $product = $line['product'];

                $order->items()->create([
                    'product_id' => $product->slug,
                    'title' => $product->title,
                    'sku' => $product->slug,
                    'quantity' => $line['quantity'],
                    'unit_price' => $product->price_value,
                    'metadata' => [
                        'source' => 'website',
                        'product_database_id' => $product->id,
                        'category' => $product->category?->slug,
                    ],
                ]);
            }

            if ($validated['fulfillment_method'] === 'delivery') {
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

            return $order->fresh(['items']);
        });

        $cart->clear();

        return redirect()->route('storefront.checkout.success', $order);
    }

    public function success(Order $order): View
    {
        return view('storefront.checkout.success', [
            'order' => $order->load('items'),
            'meta' => [
                'title' => 'سفارش ثبت شد | EtokBike',
                'description' => 'سفارش شما در فروشگاه EtokBike ثبت شد.',
                'canonical' => route('storefront.checkout.success', $order),
                'robots' => 'noindex,nofollow',
            ],
        ]);
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    private function deliveryZone(array $validated, int $subtotal): ?DeliveryZone
    {
        if (($validated['fulfillment_method'] ?? null) !== 'delivery') {
            return null;
        }

        if (blank($validated['delivery_address'] ?? null)) {
            throw ValidationException::withMessages([
                'delivery_address' => 'برای ارسال، آدرس تحویل را وارد کنید.',
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
                'delivery_zone_id' => 'جمع سفارش برای این محدوده ارسال کافی نیست.',
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
                'discount_code' => 'کد تخفیف معتبر نیست.',
            ]);
        }

        if ($discount->starts_at && $discount->starts_at->isFuture()) {
            throw ValidationException::withMessages([
                'discount_code' => 'زمان استفاده از این کد تخفیف هنوز شروع نشده است.',
            ]);
        }

        if ($discount->ends_at && $discount->ends_at->isPast()) {
            throw ValidationException::withMessages([
                'discount_code' => 'زمان استفاده از این کد تخفیف تمام شده است.',
            ]);
        }

        if ($discount->usage_limit !== null && $discount->used_count >= $discount->usage_limit) {
            throw ValidationException::withMessages([
                'discount_code' => 'ظرفیت استفاده از این کد تخفیف تمام شده است.',
            ]);
        }

        if ($subtotal < $discount->minimum_order_total) {
            throw ValidationException::withMessages([
                'discount_code' => 'جمع سفارش برای این کد تخفیف کافی نیست.',
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
