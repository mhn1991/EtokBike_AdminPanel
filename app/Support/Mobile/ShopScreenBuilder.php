<?php

namespace App\Support\Mobile;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Support\Facades\Schema;

class ShopScreenBuilder
{
    /**
     * @return array<string, mixed>
     */
    public static function build(array $fallback, ?User $user = null): array
    {
        if (! static::canUseDatabase()) {
            return $fallback;
        }

        $categories = ProductCategory::activeTreeForStorefront();

        if ($categories->isEmpty()) {
            return $fallback;
        }

        $products = Product::query()
            ->where('is_active', true)
            ->with(static::productRelations())
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
                    'parentId' => $category->parent?->slug,
                    'label' => $category->label,
                    'treeLabel' => $category->tree_label,
                    'depth' => (int) ($category->tree_depth ?? 0),
                    'productCount' => (int) ($category->active_products_count ?? 0),
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
        $variantVersion = Schema::hasTable('product_variants')
            ? ProductVariant::query()->max('updated_at')
            : null;
        $timestamp = collect([$categoryVersion, $productVersion, $variantVersion])
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

    /**
     * @return array<int|string, mixed>
     */
    private static function productRelations(): array
    {
        $relations = ['category'];

        if (Schema::hasTable('product_variants')) {
            $relations['variants'] = fn ($query) => $query
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name');
        }

        return $relations;
    }
}
