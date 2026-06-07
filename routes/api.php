<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CustomerMessageController;
use App\Http\Controllers\Api\MobileCartController;
use App\Http\Controllers\Api\MobileConfigController;
use App\Http\Controllers\Api\MobileStateController;
use App\Http\Controllers\Api\MobileTelemetryController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProgramBookingController;
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

        Route::post('telemetry', [MobileTelemetryController::class, 'store'])
            ->name('telemetry.store');

        Route::get('state', [MobileStateController::class, 'show'])
            ->name('state.show');
    });

Route::post('orders', [OrderController::class, 'store'])
    ->name('orders.store');

Route::prefix('auth')
    ->name('auth.')
    ->group(function (): void {
        Route::post('login', [AuthController::class, 'login'])
            ->name('login');

        Route::middleware('auth:sanctum')->group(function (): void {
            Route::get('user', [AuthController::class, 'user'])
                ->name('user');

            Route::post('logout', [AuthController::class, 'logout'])
                ->name('logout');
        });
    });

Route::get('cart', [MobileCartController::class, 'show'])
    ->name('cart.show');

Route::post('cart/items', [MobileCartController::class, 'store'])
    ->name('cart.items.store');

Route::patch('cart/items/{item}', [MobileCartController::class, 'update'])
    ->name('cart.items.update');

Route::delete('cart/items/{item}', [MobileCartController::class, 'destroy'])
    ->name('cart.items.destroy');

Route::post('service-bookings', [ServiceBookingController::class, 'store'])
    ->name('service-bookings.store');

Route::post('program-bookings', [ProgramBookingController::class, 'store'])
    ->name('program-bookings.store');

Route::post('messages', [CustomerMessageController::class, 'store'])
    ->name('messages.store');
