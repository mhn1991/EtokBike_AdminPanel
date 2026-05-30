<?php

namespace App\Support\Mobile;

use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class CartScreenBuilder
{
    /**
     * @return array<string, mixed>
     */
    public static function build(array $fallback): array
    {
        if (! static::canUseDatabase()) {
            return $fallback;
        }

        $products = static::cartProducts();

        if ($products->isEmpty()) {
            return $fallback;
        }

        $screen = $fallback;
        $screen['version'] = static::version($fallback);
        $total = $products->sum(fn (Product $product): int => $product->price_value);
        $summary = $products->pluck('title')->take(2)->implode(' + ');

        foreach ($screen['sections'] as &$section) {
            if (($section['id'] ?? null) === 'cart-hero') {
                $section['data']['featureTitle'] = $summary;
                $section['data']['featurePrice'] = 'جمع: '.static::formatToman($total);
                $section['data']['stats'][0]['value'] = number_format($products->count()).' قلم';
            }

            if (($section['id'] ?? null) === 'cart-summary') {
                $section['data']['total'] = static::formatToman($total);
                $section['data']['items'] = $products
                    ->map(fn (Product $product): array => static::cartItemPayload($product))
                    ->values()
                    ->all();
            }
        }

        return $screen;
    }

    public static function version(array $fallback): int
    {
        if (! static::canUseDatabase()) {
            return (int) ($fallback['version'] ?? 1);
        }

        $timestamp = Product::query()->max('updated_at');
        $version = $timestamp ? strtotime((string) $timestamp) : 0;

        return max((int) ($fallback['version'] ?? 1), $version ?: 0);
    }

    private static function canUseDatabase(): bool
    {
        return Schema::hasTable('product_categories')
            && Schema::hasTable('products');
    }

    /**
     * @return Collection<int, Product>
     */
    private static function cartProducts(): Collection
    {
        $products = Product::query()
            ->where('is_active', true)
            ->where('is_featured', true)
            ->with('category')
            ->orderBy('sort_order')
            ->orderBy('title')
            ->limit(2)
            ->get();

        if ($products->isNotEmpty()) {
            return $products;
        }

        return Product::query()
            ->where('is_active', true)
            ->with('category')
            ->orderBy('sort_order')
            ->orderBy('title')
            ->limit(2)
            ->get();
    }

    /**
     * @return array<string, string>
     */
    private static function cartItemPayload(Product $product): array
    {
        return [
            'title' => $product->title,
            'subtitle' => $product->stock_label ?: ($product->category?->label ?: 'محصول فروشگاه'),
            'description' => $product->description ?: 'انتخاب شده از موجودی پنل مدیریت.',
            'price' => $product->price_label ?: static::formatToman($product->price_value),
        ];
    }

    private static function formatToman(?int $value): string
    {
        return number_format($value ?? 0).' تومان';
    }
}
