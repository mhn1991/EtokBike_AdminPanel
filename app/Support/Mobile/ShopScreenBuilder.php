<?php

namespace App\Support\Mobile;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Support\Facades\Schema;

class ShopScreenBuilder
{
    // Keep this on the same Unix-timestamp scale as model updates so catalog edits supersede it.
    private const LAYOUT_VERSION = 1783635663;

    private const FEATURE_LABELS = [
        'brand' => 'برند',
        'brake_type' => 'نوع ترمز',
        'color' => 'رنگ',
        'colour' => 'رنگ',
        'frame_size' => 'سایز فریم',
        'gear_count' => 'تعداد دنده',
        'gender' => 'جنسیت',
        'material' => 'جنس',
        'model' => 'مدل',
        'size' => 'سایز',
        'suspension' => 'نوع کمک',
        'wheel_size' => 'سایز چرخ',
    ];

    private const FEATURE_PLACEHOLDERS = [
        'brand' => 'همه برندها',
        'brake_type' => 'همه ترمزها',
        'color' => 'همه رنگ‌ها',
        'colour' => 'همه رنگ‌ها',
        'frame_size' => 'همه سایزهای فریم',
        'gear_count' => 'همه تعداد دنده‌ها',
        'gender' => 'همه جنسیت‌ها',
        'material' => 'همه جنس‌ها',
        'model' => 'همه مدل‌ها',
        'size' => 'همه سایزها',
        'suspension' => 'همه نوع کمک‌ها',
        'wheel_size' => 'همه سایزهای چرخ',
    ];

    private const FEATURE_SORT_ORDER = [
        'brand' => 10,
        'model' => 20,
        'color' => 30,
        'colour' => 30,
        'size' => 40,
        'frame_size' => 45,
        'wheel_size' => 46,
        'suspension' => 47,
        'brake_type' => 48,
        'gear_count' => 49,
        'material' => 50,
        'gender' => 60,
    ];

    /**
     * @return array<string, mixed>
     */
    public static function build(array $fallback, ?User $user = null): array
    {
        if (! static::canUseDatabase()) {
            return $fallback;
        }

        $categories = ProductCategory::activeTreeForStorefront();
        $products = $categories->isEmpty()
            ? collect()
            : Product::query()
                ->where('is_active', true)
                ->with(static::productRelations())
                ->orderBy('sort_order')
                ->orderBy('title')
                ->get();

        $screen = $fallback;
        $screen['version'] = static::version($fallback);
        $screen['sections'] = array_values(array_filter(
            $screen['sections'],
            fn (array $section): bool => ! in_array($section['id'] ?? '', ['shop-hero', 'shop-shortcuts', 'shop-benefits'], true),
        ));

        foreach ($screen['sections'] as &$section) {
            if (($section['type'] ?? null) !== 'product_list') {
                continue;
            }

            $section['data'] = [
                ...($section['data'] ?? []),
                ...static::productListPresentation($products),
            ];
            $section['data']['defaultCategory'] = '';
            if ($categories->isNotEmpty()) {
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
        }

        return $screen;
    }

    public static function version(array $fallback): int
    {
        if (! static::canUseDatabase()) {
            return max((int) ($fallback['version'] ?? 1), self::LAYOUT_VERSION);
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

        return max((int) ($fallback['version'] ?? 1), $timestamp ?: 0, self::LAYOUT_VERSION);
    }

    /**
     * @param  \Illuminate\Support\Collection<int, Product>  $products
     * @return array<string, mixed>
     */
    private static function productListPresentation($products): array
    {
        return [
            'title' => 'محصولات فروشگاه',
            'hideTitle' => true,
            'searchLabel' => 'جستجوی محصول',
            'searchPlaceholder' => 'نام دوچرخه، قطعه یا لوازم جانبی',
            'searchActionLabel' => 'جستجو',
            'categoryLabel' => 'دسته‌بندی‌ها',
            'categoryMenuLabel' => 'دسته‌بندی‌ها',
            'allCategoriesLabel' => 'همه محصولات',
            'quickFilterIds' => ['availability', 'sort'],
            'filtersTitle' => 'فیلترهای بیشتر',
            'filtersCollapsedLabel' => 'فیلترهای بیشتر',
            'filtersExpandedLabel' => 'بستن فیلترها',
            'applyFiltersLabel' => 'اعمال فیلتر',
            'resetFiltersLabel' => 'حذف فیلترها',
            'emptyStateText' => 'محصولی برای این دسته‌بندی و فیلترها پیدا نشد.',
            'emptyStateTitle' => 'محصولی با این فیلترها نداریم',
            'emptyStateDescription' => 'فیلترها را سبک‌تر کنید، همه محصولات را ببینید یا برای انتخاب قطعه مناسب پیام بفرستید.',
            'emptyStateMessageLabel' => 'ارسال پیام',
            'maxPriceFilter' => [
                'id' => 'maxPrice',
                'label' => 'قیمت کمتر از',
                'default' => '',
                'placeholder' => '15000000',
            ],
            'initialItems' => 4,
            'pageSize' => 4,
            'loadMoreLabel' => 'نمایش محصولات بیشتر',
            'filters' => static::filtersFor($products),
        ];
    }

    /**
     * @param  \Illuminate\Support\Collection<int, Product>  $products
     * @return array<int, array<string, mixed>>
     */
    private static function filtersFor($products): array
    {
        $featureValues = [];

        if (Schema::hasTable('product_variants')) {
            foreach ($products as $product) {
                foreach ($product->variants as $variant) {
                    foreach (static::variantFeatureValues($variant->options ?? []) as $key => $value) {
                        $featureValues[$key][$value] = true;
                    }
                }
            }
        }

        $features = collect($featureValues)
            ->sortKeys()
            ->map(function (array $values, string $key): array {
                $sortedValues = array_keys($values);
                sort($sortedValues, SORT_NATURAL | SORT_FLAG_CASE);

                return [
                    'key' => $key,
                    'order' => self::FEATURE_SORT_ORDER[$key] ?? 999,
                    'filter' => [
                        'id' => 'feature:'.$key,
                        'label' => self::FEATURE_LABELS[$key] ?? str_replace('_', ' ', $key),
                        'default' => '',
                        'options' => [
                            [
                                'id' => '',
                                'label' => self::FEATURE_PLACEHOLDERS[$key] ?? 'همه گزینه‌ها',
                            ],
                            ...array_map(fn (string $value): array => ['id' => $value, 'label' => $value], $sortedValues),
                        ],
                    ],
                ];
            })
            ->sortBy([
                ['order', 'asc'],
                ['key', 'asc'],
            ])
            ->pluck('filter')
            ->values()
            ->all();

        return [
            [
                'id' => 'availability',
                'label' => 'موجودی',
                'default' => 'all',
                'options' => [
                    ['id' => 'all', 'label' => 'همه وضعیت‌ها'],
                    ['id' => 'in_stock', 'label' => 'موجود'],
                    ['id' => 'low_stock', 'label' => 'موجودی محدود'],
                    ['id' => 'orderable', 'label' => 'قابل سفارش'],
                    ['id' => 'out_of_stock', 'label' => 'ناموجود'],
                ],
            ],
            [
                'id' => 'sort',
                'label' => 'مرتب‌سازی',
                'default' => 'recommended',
                'options' => [
                    ['id' => 'recommended', 'label' => 'پیشنهادی'],
                    ['id' => 'price_low', 'label' => 'ارزان‌ترین'],
                    ['id' => 'price_high', 'label' => 'گران‌ترین'],
                    ['id' => 'newest', 'label' => 'جدیدترین'],
                ],
            ],
            [
                'id' => 'priceBand',
                'label' => 'بازه قیمت',
                'default' => 'all',
                'options' => [
                    ['id' => 'all', 'label' => 'همه قیمت‌ها'],
                    ['id' => 'under_2m', 'label' => 'زیر ۲ میلیون'],
                    ['id' => '2m_20m', 'label' => '۲ تا ۲۰ میلیون'],
                    ['id' => 'over_20m', 'label' => 'بالای ۲۰ میلیون'],
                ],
            ],
            [
                'id' => 'maxPrice',
                'label' => 'قیمت کمتر از',
                'default' => '',
                'placeholder' => '15000000',
            ],
            ...$features,
        ];
    }

    /**
     * @param  array<string, mixed>  $options
     * @return array<string, string>
     */
    private static function variantFeatureValues(array $options): array
    {
        $values = [];

        foreach (['color', 'size'] as $key) {
            if (filled($options[$key] ?? null) && ! is_array($options[$key])) {
                $values[$key] = (string) $options[$key];
            }
        }

        foreach (($options['attributes'] ?? []) as $key => $value) {
            if (filled($value) && ! is_array($value)) {
                $values[(string) $key] = (string) $value;
            }
        }

        foreach ($options as $key => $value) {
            if (in_array($key, ['color', 'size', 'attributes'], true) || blank($value) || is_array($value)) {
                continue;
            }

            $values[(string) $key] = (string) $value;
        }

        return $values;
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
