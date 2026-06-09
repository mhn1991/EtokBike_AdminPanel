<?php

namespace App\Support\Mobile;

use App\Models\DeliveryMethod;
use Illuminate\Support\Facades\Schema;

class CartScreenBuilder
{
    /**
     * @return array<string, mixed>
     */
    public static function build(array $fallback): array
    {
        $screen = $fallback;
        $screen['version'] = static::version($fallback);

        foreach ($screen['sections'] as &$section) {
            if (($section['id'] ?? null) === 'cart-hero') {
                $section['data']['featureTitle'] = 'سبد شما پس از افزودن محصول اینجا نمایش داده می‌شود';
                $section['data']['featureSubtitle'] = 'آیتم‌ها، تعداد و جمع پرداخت از سبد واقعی اپ دریافت می‌شود.';
                $section['data']['featurePrice'] = 'در انتظار انتخاب محصول';
                $section['data']['stats'][0]['value'] = 'زنده';
            }

            if (($section['id'] ?? null) === 'cart-summary') {
                $section['data']['total'] = '';
                $section['data']['items'] = [];
                $section['data']['emptyStateText'] = 'سبد خرید شما خالی است. از فروشگاه محصول اضافه کنید.';
            }

            if (($section['id'] ?? null) === 'delivery-options') {
                $section['data']['items'] = static::deliveryMethods($section['data']['items'] ?? []);
            }
        }

        return $screen;
    }

    public static function version(array $fallback): int
    {
        if (! Schema::hasTable('delivery_methods')) {
            return (int) ($fallback['version'] ?? 1);
        }

        $timestamp = collect([DeliveryMethod::query()->max('updated_at')])
            ->filter()
            ->map(fn ($value): int => strtotime((string) $value) ?: 0)
            ->max();

        return max((int) ($fallback['version'] ?? 1), $timestamp ?: 0);
    }

    /**
     * @param  array<int, array<string, mixed>>  $fallback
     * @return array<int, array<string, mixed>>
     */
    private static function deliveryMethods(array $fallback): array
    {
        if (! Schema::hasTable('delivery_methods')) {
            return $fallback;
        }

        $methods = DeliveryMethod::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get()
            ->map(fn (DeliveryMethod $method): array => $method->toMobilePayload())
            ->values()
            ->all();

        return empty($methods) ? $fallback : $methods;
    }
}
