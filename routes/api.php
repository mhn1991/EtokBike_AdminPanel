<?php

use App\Http\Controllers\Api\CustomerMessageController;
use App\Http\Controllers\Api\MobileConfigController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ServiceBookingController;
use Illuminate\Support\Facades\Route;

Route::prefix('mobile')
    ->name('mobile.')
    ->group(function (): void {
        Route::get('manifest', [MobileConfigController::class, 'manifest'])
            ->name('manifest');

        Route::get('screens/{screen}', [MobileConfigController::class, 'screen'])
            ->whereAlphaNumeric('screen')
            ->name('screens.show');
    });

Route::post('orders', [OrderController::class, 'store'])
    ->name('orders.store');

Route::post('service-bookings', [ServiceBookingController::class, 'store'])
    ->name('service-bookings.store');

Route::post('messages', [CustomerMessageController::class, 'store'])
    ->name('messages.store');
