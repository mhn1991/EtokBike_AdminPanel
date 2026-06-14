<?php

use App\Http\Controllers\Admin\ReceiptPrintController;
use App\Http\Controllers\Storefront\AccountController;
use App\Http\Controllers\Storefront\CartController;
use App\Http\Controllers\Storefront\CheckoutController;
use App\Http\Controllers\Storefront\EventController;
use App\Http\Controllers\Storefront\HomeController;
use App\Http\Controllers\Storefront\MessageController;
use App\Http\Controllers\Storefront\PageController;
use App\Http\Controllers\Storefront\SeoController;
use App\Http\Controllers\Storefront\SeoRedirectController;
use App\Http\Controllers\Storefront\ServiceController;
use App\Http\Controllers\Storefront\ShopController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->prefix('admin')->group(function (): void {
    Route::get('receipts/{receipt}/print', ReceiptPrintController::class)
        ->name('admin.receipts.print');
});

Route::get('/', HomeController::class)
    ->name('storefront.home');

Route::get('shop', [ShopController::class, 'index'])
    ->name('storefront.shop');

Route::get('shop/category/{category:slug}', [ShopController::class, 'category'])
    ->name('storefront.categories.show');

Route::get('shop/products/{product:slug}', [ShopController::class, 'show'])
    ->name('storefront.products.show');

Route::get('services', [ServiceController::class, 'index'])
    ->name('storefront.services');

Route::post('services/bookings', [ServiceController::class, 'store'])
    ->name('storefront.services.bookings.store');

Route::get('events', [EventController::class, 'index'])
    ->name('storefront.events');

Route::get('events/{program:slug}', [EventController::class, 'show'])
    ->name('storefront.events.show');

Route::post('events/{program:slug}/bookings', [EventController::class, 'book'])
    ->name('storefront.events.bookings.store');

Route::get('messages', [MessageController::class, 'index'])
    ->name('storefront.messages');

Route::post('messages', [MessageController::class, 'store'])
    ->name('storefront.messages.store');

Route::get('account', [AccountController::class, 'show'])
    ->name('storefront.account');

Route::get('cart', [CartController::class, 'show'])
    ->name('storefront.cart.show');

Route::post('cart/items/{product:slug}', [CartController::class, 'store'])
    ->name('storefront.cart.items.store');

Route::patch('cart/items/{product:slug}', [CartController::class, 'update'])
    ->name('storefront.cart.items.update');

Route::delete('cart/items/{product:slug}', [CartController::class, 'destroy'])
    ->name('storefront.cart.items.destroy');

Route::get('checkout', [CheckoutController::class, 'show'])
    ->name('storefront.checkout.show');

Route::post('checkout', [CheckoutController::class, 'store'])
    ->name('storefront.checkout.store');

Route::get('orders/{order:order_number}/thank-you', [CheckoutController::class, 'success'])
    ->name('storefront.checkout.success');

Route::get('pages/{page:slug}', [PageController::class, 'show'])
    ->name('storefront.pages.show');

Route::get('sitemap.xml', [SeoController::class, 'sitemap'])
    ->name('storefront.sitemap');

Route::get('robots.txt', [SeoController::class, 'robots'])
    ->name('storefront.robots');

Route::get('{path}', SeoRedirectController::class)
    ->where('path', '.*')
    ->name('storefront.redirects.resolve');
