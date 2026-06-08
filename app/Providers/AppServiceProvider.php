<?php

namespace App\Providers;

use App\Support\Storefront\StorefrontCart;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('storefront.*', function ($view): void {
            $view->with('cartCount', app(StorefrontCart::class)->count());
        });
    }
}
