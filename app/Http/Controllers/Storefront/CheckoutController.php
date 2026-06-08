<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Support\Storefront\StorefrontCart;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
            'meta' => [
                'title' => 'تکمیل سفارش | EtokBike',
                'description' => 'تکمیل سفارش فروشگاه EtokBike.',
                'canonical' => route('storefront.checkout.show'),
                'robots' => 'noindex,nofollow',
            ],
        ]);
    }

    public function store(Request $request, StorefrontCart $cart): RedirectResponse
    {
        if ($cart->isEmpty()) {
            return redirect()->route('storefront.shop');
        }

        $validated = $request->validate([
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_email' => ['nullable', 'email', 'max:255'],
            'customer_phone' => ['required', 'string', 'max:255'],
            'fulfillment_method' => ['required', 'string', 'in:pickup,delivery'],
            'customer_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $lines = $cart->lines();

        if ($lines->isEmpty()) {
            $cart->clear();

            return redirect()->route('storefront.shop');
        }

        $order = DB::transaction(function () use ($validated, $lines): Order {
            $order = Order::query()->create([
                'customer_name' => $validated['customer_name'],
                'customer_email' => $validated['customer_email'] ?? null,
                'customer_phone' => $validated['customer_phone'],
                'fulfillment_method' => $validated['fulfillment_method'],
                'customer_notes' => $validated['customer_notes'] ?? null,
                'status' => 'pending',
                'payment_status' => 'unpaid',
                'currency' => 'IRR',
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
}
