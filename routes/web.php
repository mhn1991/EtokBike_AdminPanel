<?php

use App\Http\Controllers\Storefront\CartController;
use App\Http\Controllers\Storefront\CheckoutController;
use App\Http\Controllers\Storefront\HomeController;
use App\Http\Controllers\Storefront\SeoController;
use App\Http\Controllers\Storefront\ShopController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)
    ->name('storefront.home');

Route::get('shop', [ShopController::class, 'index'])
    ->name('storefront.shop');

Route::get('shop/category/{category:slug}', [ShopController::class, 'category'])
    ->name('storefront.categories.show');

Route::get('shop/products/{product:slug}', [ShopController::class, 'show'])
    ->name('storefront.products.show');

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

Route::get('sitemap.xml', [SeoController::class, 'sitemap'])
    ->name('storefront.sitemap');

Route::get('robots.txt', [SeoController::class, 'robots'])
    ->name('storefront.robots');
