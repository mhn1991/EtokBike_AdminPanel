<?php

namespace App\Support\Storefront;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\SeoSetting;
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

    public static function setting(): ?SeoSetting
    {
        return SeoSetting::active();
    }

    public static function siteName(): string
    {
        return static::setting()?->site_name ?: 'EtokBike';
    }

    public static function defaultTitle(string $fallback = 'EtokBike'): string
    {
        return static::setting()?->default_title ?: $fallback;
    }

    public static function defaultDescription(string $fallback = 'فروشگاه دوچرخه، قطعات و لوازم جانبی EtokBike.'): string
    {
        return static::setting()?->default_description ?: $fallback;
    }

    public static function defaultImage(): string
    {
        return static::image(static::setting()?->default_og_image);
    }

    public static function productTitle(Product $product): string
    {
        return $product->seo_title ?: $product->title.' | '.static::siteName();
    }

    public static function productDescription(Product $product): string
    {
        return static::description($product->seo_description, $product->description ?: $product->subtitle);
    }

    public static function productImage(Product $product): string
    {
        return static::image($product->og_image ?: ImageUrl::resolve($product->image_url));
    }

    public static function categoryDescription(?ProductCategory $category = null): string
    {
        if ($category) {
            return static::description(
                $category->seo_description,
                'خرید محصولات دسته '.$category->label.' از فروشگاه EtokBike با موجودی، قیمت و ثبت سفارش آنلاین.',
            );
        }

        return static::description(
            static::setting()?->default_description,
            'خرید دوچرخه، قطعات و لوازم جانبی از فروشگاه EtokBike با فیلتر موجودی، قیمت و دسته‌بندی.',
        );
    }

    /**
     * @return array<string, mixed>
     */
    public static function organization(): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'Store',
            'name' => static::siteName(),
            'url' => route('storefront.home'),
            'image' => static::defaultImage(),
            'sameAs' => array_values(array_filter(static::setting()?->social_profiles ?? [])),
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
        return [
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => $product->title,
            'description' => self::productDescription($product),
            'image' => [self::productImage($product)],
            'sku' => $product->sku ?: $product->slug,
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
            ? ($category->seo_title ?: $category->label.' | فروشگاه '.static::siteName())
            : static::defaultTitle('فروشگاه دوچرخه '.static::siteName());
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
