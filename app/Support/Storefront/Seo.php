<?php

namespace App\Support\Storefront;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Support\Mobile\ImageUrl;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class Seo
{
    public static function description(?string $value, string $fallback, int $limit = 155): string
    {
        $text = trim(preg_replace('/\s+/', ' ', strip_tags((string) ($value ?: $fallback))) ?: $fallback);

        return Str::limit($text, $limit, '');
    }

    public static function image(?string $value = null): string
    {
        if ($value && Str::startsWith($value, ['http://', 'https://'])) {
            return $value;
        }

        if ($value) {
            return url($value);
        }

        return asset('images/storefront/hero-shop.png');
    }

    /**
     * @return array<string, mixed>
     */
    public static function organization(): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'Store',
            'name' => 'EtokBike',
            'url' => route('storefront.home'),
            'image' => asset('images/storefront/hero-shop.png'),
            'sameAs' => [],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function breadcrumbs(array $items): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => collect($items)
                ->values()
                ->map(fn (array $item, int $index): array => [
                    '@type' => 'ListItem',
                    'position' => $index + 1,
                    'name' => $item['name'],
                    'item' => $item['url'],
                ])
                ->all(),
        ];
    }

    /**
     * @param  Collection<int, Product>  $products
     * @return array<string, mixed>
     */
    public static function itemList(Collection $products, string $url): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'ItemList',
            'url' => $url,
            'itemListElement' => $products
                ->values()
                ->map(fn (Product $product, int $index): array => [
                    '@type' => 'ListItem',
                    'position' => $index + 1,
                    'url' => route('storefront.products.show', $product),
                    'name' => $product->title,
                ])
                ->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function product(Product $product): array
    {
        $image = ImageUrl::resolve($product->image_url);

        return [
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => $product->title,
            'description' => self::description($product->description, $product->subtitle),
            'image' => [self::image($image)],
            'sku' => $product->slug,
            'category' => $product->category?->label,
            'offers' => [
                '@type' => 'Offer',
                'url' => route('storefront.products.show', $product),
                'priceCurrency' => 'IRR',
                'price' => $product->price_value,
                'availability' => self::availability($product->availability),
                'itemCondition' => 'https://schema.org/NewCondition',
            ],
        ];
    }

    public static function categoryTitle(?ProductCategory $category = null): string
    {
        return $category
            ? $category->label.' | فروشگاه EtokBike'
            : 'فروشگاه دوچرخه EtokBike';
    }

    private static function availability(string $availability): string
    {
        return match ($availability) {
            'out_of_stock' => 'https://schema.org/OutOfStock',
            'low_stock', 'in_stock' => 'https://schema.org/InStock',
            'orderable' => 'https://schema.org/PreOrder',
            default => 'https://schema.org/InStock',
        };
    }
}
