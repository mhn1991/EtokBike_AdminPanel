<?php

namespace App\Support\Mobile;

use App\Models\Product;
use Illuminate\Support\Facades\Schema;

class BundleOfferResolver
{
    /**
     * Resolve a bundle_offers section's stored data into mobile-ready payload:
     * component chips and the "was" price are derived from the linked products
     * at read time, so they can never drift out of sync with the catalog.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function resolve(array $data): array
    {
        if (! Schema::hasTable('products') || empty($data['items']) || ! is_array($data['items'])) {
            return $data;
        }

        $data['items'] = array_map(
            fn (mixed $item): mixed => is_array($item) ? static::resolveItem($item) : $item,
            $data['items']
        );

        return $data;
    }

    /**
     * @param  array<string, mixed>  $item
     * @return array<string, mixed>
     */
    private static function resolveItem(array $item): array
    {
        $slugs = array_values(array_filter((array) ($item['productIds'] ?? [])));

        if (empty($slugs)) {
            return $item;
        }

        $products = Product::query()
            ->whereIn('slug', $slugs)
            ->where('is_active', true)
            ->get()
            ->keyBy('slug');

        // Preserve the order the admin picked the products in.
        $ordered = collect($slugs)
            ->map(fn (string $slug): ?Product => $products->get($slug))
            ->filter()
            ->values();

        if ($ordered->isEmpty()) {
            return $item;
        }

        $item['components'] = $ordered
            ->map(fn (Product $product): array => [
                'text' => $product->thumbnail_text ?: mb_strtoupper(mb_substr($product->title, 0, 4)),
                'color' => $product->thumbnail_color ?: '#101114',
            ])
            ->all();

        if (blank($item['wasPrice'] ?? null)) {
            $sum = (int) $ordered->sum('price_value');
            $item['wasPriceValue'] = $sum;
            $item['wasPrice'] = static::toPersianDigits(number_format($sum)).' تومان';
        }

        return $item;
    }

    // The mobile app displays every other price with Persian numerals, so the
    // auto-computed "was" price needs to match rather than showing 0-9.
    private static function toPersianDigits(string $value): string
    {
        return strtr($value, [
            '0' => '۰', '1' => '۱', '2' => '۲', '3' => '۳', '4' => '۴',
            '5' => '۵', '6' => '۶', '7' => '۷', '8' => '۸', '9' => '۹',
        ]);
    }
}
