<?php

namespace App\Support\Mobile;

use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Support\Facades\Schema;

class ShopScreenBuilder
{
    /**
     * @return array<string, mixed>
     */
    public static function build(array $fallback, ?\App\Models\User $user = null): array
    {
        if (! static::canUseDatabase()) {
            return $fallback;
        }

        $categories = ProductCategory::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('label')
            ->get();

        if ($categories->isEmpty()) {
            return $fallback;
        }

        $products = Product::query()
            ->where('is_active', true)
            ->with('category')
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get();

        $screen = $fallback;
        $screen['version'] = static::version($fallback);

        foreach ($screen['sections'] as &$section) {
            if (($section['type'] ?? null) !== 'product_list') {
                continue;
            }

            $section['data']['defaultCategory'] = $categories->first()->slug;
            $section['data']['categories'] = $categories
                ->map(fn (ProductCategory $category): array => [
                    'id' => $category->slug,
                    'label' => $category->label,
                ])
                ->values()
                ->all();
            $section['data']['items'] = $products
                ->map(fn (Product $product): array => $product->toMobilePayload())
                ->values()
                ->all();
        }

        return $screen;
    }

    public static function version(array $fallback): int
    {
        if (! static::canUseDatabase()) {
            return (int) ($fallback['version'] ?? 1);
        }

        $categoryVersion = ProductCategory::query()->max('updated_at');
        $productVersion = Product::query()->max('updated_at');
        $timestamp = collect([$categoryVersion, $productVersion])
            ->filter()
            ->map(fn ($value): int => strtotime((string) $value) ?: 0)
            ->max();

        return max((int) ($fallback['version'] ?? 1), $timestamp ?: 0);
    }

    private static function canUseDatabase(): bool
    {
        return Schema::hasTable('product_categories')
            && Schema::hasTable('products');
    }
}
