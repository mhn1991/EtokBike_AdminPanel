<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Support\Storefront\StorefrontCart;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function show(StorefrontCart $cart): View
    {
        return view('storefront.cart.show', [
            'lines' => $cart->lines(),
            'subtotal' => $cart->subtotal(),
            'meta' => [
                'title' => 'سبد خرید | EtokBike',
                'description' => 'سبد خرید فروشگاه EtokBike.',
                'canonical' => route('storefront.cart.show'),
                'robots' => 'noindex,nofollow',
            ],
        ]);
    }

    public function store(Request $request, Product $product, StorefrontCart $cart): RedirectResponse
    {
        abort_unless($product->is_active && $product->availability !== 'out_of_stock', 404);

        $validated = $request->validate([
            'quantity' => ['nullable', 'integer', 'min:1', 'max:20'],
        ]);

        $cart->add($product, (int) ($validated['quantity'] ?? 1));

        return redirect()
            ->route('storefront.cart.show')
            ->with('status', 'محصول به سبد خرید اضافه شد.');
    }

    public function update(Request $request, Product $product, StorefrontCart $cart): RedirectResponse
    {
        abort_unless($product->is_active, 404);

        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:20'],
        ]);

        $cart->update($product, (int) $validated['quantity']);

        return redirect()
            ->route('storefront.cart.show')
            ->with('status', 'سبد خرید به‌روز شد.');
    }

    public function destroy(Product $product, StorefrontCart $cart): RedirectResponse
    {
        $cart->remove($product);

        return redirect()
            ->route('storefront.cart.show')
            ->with('status', 'محصول از سبد خرید حذف شد.');
    }
}
