<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductVariant;
use App\Support\Storefront\Seo;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class ShopController extends Controller
{
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

    public function index(Request $request): View
    {
        return $this->listing($request);
    }

    public function category(Request $request, ProductCategory $category): View
    {
        abort_unless($category->is_active, 404);

        return $this->listing($request, $category);
    }

    public function show(Product $product): View
    {
        abort_unless($product->is_active && $product->category?->is_active, 404);

        $relatedProducts = Product::query()
            ->with('category')
            ->where('is_active', true)
            ->where('product_category_id', $product->product_category_id)
            ->whereKeyNot($product->id)
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->limit(4)
            ->get();

        return view('storefront.shop.show', [
            'product' => $product,
            'relatedProducts' => $relatedProducts,
            'meta' => [
                'title' => Seo::productTitle($product),
                'description' => Seo::productDescription($product),
                'canonical' => $product->canonical_url ?: route('storefront.products.show', $product),
                'robots' => $product->robots ?: 'index,follow',
                'image' => Seo::productImage($product),
                'ogTitle' => $product->og_title ?: Seo::productTitle($product),
                'ogDescription' => $product->og_description ?: Seo::productDescription($product),
            ],
            'structuredData' => [
                Seo::product($product),
                Seo::breadcrumbs([
                    ['name' => 'EtokBike', 'url' => route('storefront.home')],
                    ['name' => 'فروشگاه', 'url' => route('storefront.shop')],
                    ...$this->categoryBreadcrumbItems($product->category),
                    ['name' => $product->title, 'url' => route('storefront.products.show', $product)],
                ]),
            ],
        ]);
    }

    private function listing(Request $request, ?ProductCategory $category = null): View
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'availability' => ['nullable', 'string', 'in:in_stock,low_stock,orderable,out_of_stock'],
            'price' => ['nullable', 'string', 'in:under_2m,2m_20m,over_20m'],
            'max_price' => ['nullable', 'integer', 'min:0'],
            'features' => ['nullable', 'array'],
            'features.*' => ['nullable', 'string', 'max:120'],
            'color' => ['nullable', 'string', 'max:80'],
            'size' => ['nullable', 'string', 'max:80'],
            'sort' => ['nullable', 'string', 'in:recommended,price_low,price_high,newest'],
        ]);

        $maxPrice = filled($validated['max_price'] ?? null) ? (int) $validated['max_price'] : null;
        $variantFilterScope = $this->variantFilterScope($category, $validated);
        $featureFilters = $this->featureFilterOptions($variantFilterScope);
        $selectedFeatures = $this->selectedFeatureFilters($validated, $featureFilters);
        $featureFilters = $this->featureFilterOptions($variantFilterScope, $selectedFeatures, $maxPrice);
        $selectedFeatures = $this->selectedFeatureFilters(
            $this->viewFilters($validated, $selectedFeatures),
            $featureFilters,
        );
        $featureFilters = $this->featureFilterOptions($variantFilterScope, $selectedFeatures, $maxPrice);

        $query = Product::query()
            ->with('category')
            ->where('is_active', true)
            ->whereHas('category', fn (Builder $query) => $query->where('is_active', true));

        if ($category) {
            $query->whereIn('product_category_id', $category->descendantAndSelfIds());
        }

        $this->applyProductListingFilters($query, $validated);

        if ($selectedFeatures->isNotEmpty()) {
            $query->whereIn('id', $this->matchingVariantProductIds($variantFilterScope, $selectedFeatures, $maxPrice));
        } elseif ($maxPrice !== null) {
            $matchingVariantProductIds = $this->matchingVariantProductIds($variantFilterScope, collect(), $maxPrice);

            $query->where(function (Builder $query) use ($maxPrice, $matchingVariantProductIds): void {
                $query
                    ->where('price_value', '<=', $maxPrice)
                    ->orWhereIn('id', $matchingVariantProductIds);
            });
        }

        match ($validated['sort'] ?? 'recommended') {
            'price_low' => $query->orderBy('price_value'),
            'price_high' => $query->orderByDesc('price_value'),
            'newest' => $query->latest(),
            default => $query->orderByDesc('is_featured')->orderBy('sort_order')->orderBy('title'),
        };

        $products = $query->paginate(12)->withQueryString();
        $categories = ProductCategory::activeTreeForStorefront();
        $filters = $this->viewFilters($validated, $selectedFeatures);

        $hasFilters = $selectedFeatures->isNotEmpty()
            || collect($filters)->except('features')->filter(fn (mixed $value): bool => ! blank($value))->isNotEmpty();
        $canonical = $category
            ? route('storefront.categories.show', $category)
            : route('storefront.shop');

        return view('storefront.shop.index', [
            'category' => $category,
            'categories' => $categories,
            'products' => $products,
            'filters' => $filters,
            'featureFilters' => $featureFilters,
            'meta' => [
                'title' => Seo::categoryTitle($category),
                'description' => Seo::categoryDescription($category),
                'canonical' => $category?->canonical_url ?: $canonical,
                'robots' => $hasFilters ? 'noindex,follow' : ($category?->robots ?: 'index,follow'),
                'image' => Seo::image($category?->og_image),
                'ogTitle' => $category?->og_title ?: Seo::categoryTitle($category),
                'ogDescription' => $category?->og_description ?: Seo::categoryDescription($category),
            ],
            'structuredData' => [
                Seo::breadcrumbs(array_filter([
                    ['name' => 'EtokBike', 'url' => route('storefront.home')],
                    ['name' => 'فروشگاه', 'url' => route('storefront.shop')],
                    ...($category ? $this->categoryBreadcrumbItems($category) : []),
                ])),
                Seo::itemList($products->getCollection(), $canonical),
            ],
        ]);
    }

    /**
     * @return array<int, array{name: string, url: string}>
     */
    private function categoryBreadcrumbItems(ProductCategory $category): array
    {
        return $category
            ->breadcrumbCategories()
            ->map(fn (ProductCategory $category): array => [
                'name' => $category->label,
                'url' => route('storefront.categories.show', $category),
            ])
            ->all();
    }

    /**
     * @return Collection<int, ProductVariant>
     */
    private function variantFilterScope(?ProductCategory $category, array $validated): Collection
    {
        $categoryIds = $category?->descendantAndSelfIds();

        return ProductVariant::query()
            ->with('product:id,product_category_id,price_value,is_active')
            ->where('is_active', true)
            ->whereHas('product', function (Builder $query) use ($categoryIds, $validated): void {
                $query
                    ->where('is_active', true)
                    ->whereHas('category', fn (Builder $query) => $query->where('is_active', true))
                    ->when($categoryIds, fn (Builder $query) => $query->whereIn('product_category_id', $categoryIds));

                $this->applyProductListingFilters($query, $validated);
            })
            ->get(['id', 'product_id', 'options', 'price_value']);
    }

    /**
     * @param  Collection<int, ProductVariant>  $variants
     * @return array{features: array<int, array{key: string, label: string, placeholder: string, values: array<int, string>}>}
     */
    private function featureFilterOptions(Collection $variants, ?Collection $selectedFeatures = null, ?int $maxPrice = null): array
    {
        $features = [];
        $selectedFeatures ??= collect();

        $featureKeys = $variants
            ->flatMap(fn (ProductVariant $variant): array => array_keys($this->variantFeatureValues($variant)))
            ->unique()
            ->values();

        foreach ($featureKeys as $featureKey) {
            $candidateFilters = $selectedFeatures->except($featureKey);

            $candidateVariants = $variants
                ->filter(fn (ProductVariant $variant): bool => $this->variantMatchesFilters($variant, $candidateFilters, $maxPrice));

            foreach ($candidateVariants as $variant) {
                $variantFeatures = $this->variantFeatureValues($variant);

                if (! array_key_exists($featureKey, $variantFeatures)) {
                    continue;
                }

                $feature = $variantFeatures[$featureKey];
                $features[$featureKey] ??= [
                    'key' => $featureKey,
                    'label' => $this->featureLabel($featureKey, $feature['source']),
                    'placeholder' => $this->featurePlaceholder($featureKey, $feature['source']),
                    'values' => [],
                ];

                $features[$featureKey]['values'][$feature['value']] = $feature['value'];
            }
        }

        $features = collect($features)
            ->filter(fn (array $feature): bool => $feature['values'] !== []);

        return [
            'features' => $features
                ->map(function (array $feature): array {
                    $feature['values'] = collect($feature['values'])
                        ->values()
                        ->sort(SORT_NATURAL | SORT_FLAG_CASE)
                        ->values()
                        ->all();

                    return $feature;
                })
                ->sortBy(fn (array $feature): string => sprintf(
                    '%03d-%s',
                    self::FEATURE_SORT_ORDER[$feature['key']] ?? 999,
                    $feature['label'],
                ))
                ->values()
                ->all(),
        ];
    }

    private function applyProductListingFilters(Builder $query, array $validated): void
    {
        if (! blank($validated['q'] ?? null)) {
            $search = trim((string) $validated['q']);
            $query->where(function (Builder $query) use ($search): void {
                $query
                    ->where('title', 'like', '%'.$search.'%')
                    ->orWhere('subtitle', 'like', '%'.$search.'%')
                    ->orWhere('description', 'like', '%'.$search.'%');
            });
        }

        if (! blank($validated['availability'] ?? null)) {
            $query->where('availability', $validated['availability']);
        }

        match ($validated['price'] ?? null) {
            'under_2m' => $query->where('price_value', '<', 2000000),
            '2m_20m' => $query->whereBetween('price_value', [2000000, 20000000]),
            'over_20m' => $query->where('price_value', '>', 20000000),
            default => null,
        };
    }

    /**
     * @param  Collection<string, string>  $filters
     */
    private function variantMatchesFilters(ProductVariant $variant, Collection $filters, ?int $maxPrice): bool
    {
        $features = collect($this->variantFeatureValues($variant))
            ->map(fn (array $feature): string => $feature['value']);

        foreach ($filters as $key => $value) {
            if ($features->get($key) !== $value) {
                return false;
            }
        }

        if ($maxPrice === null) {
            return true;
        }

        return $variant->effectivePriceValue($variant->product) <= $maxPrice;
    }

    /**
     * @param  Collection<int, ProductVariant>  $variants
     * @param  Collection<string, string>  $filters
     * @return array<int, int>
     */
    private function matchingVariantProductIds(Collection $variants, Collection $filters, ?int $maxPrice): array
    {
        return $variants
            ->filter(fn (ProductVariant $variant): bool => $this->variantMatchesFilters($variant, $filters, $maxPrice))
            ->pluck('product_id')
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @param  array<string, mixed>  $validated
     * @param  array{features: array<int, array{key: string, label: string, placeholder: string, values: array<int, string>}>}  $featureFilters
     * @return Collection<string, string>
     */
    private function selectedFeatureFilters(array $validated, array $featureFilters): Collection
    {
        $availableFeatureValues = collect($featureFilters['features'])
            ->mapWithKeys(fn (array $feature): array => [
                $feature['key'] => collect($feature['values'])
                    ->mapWithKeys(fn (string $value): array => [$value => true]),
            ]);

        $requestedFeatures = collect($validated['features'] ?? [])
            ->mapWithKeys(function (mixed $value, string|int $key): array {
                $normalizedKey = $this->normalizeFeatureKey((string) $key);

                return $normalizedKey ? [$normalizedKey => $value] : [];
            });

        foreach (['color', 'size'] as $legacyKey) {
            if (! blank($validated[$legacyKey] ?? null)) {
                $requestedFeatures->put($legacyKey, $validated[$legacyKey]);
            }
        }

        return $requestedFeatures
            ->map(fn (mixed $value): string => trim((string) $value))
            ->filter(function (string $value, string $key) use ($availableFeatureValues): bool {
                return ! blank($value)
                    && $availableFeatureValues->has($key)
                    && $availableFeatureValues->get($key)->has($value);
            });
    }

    /**
     * @param  array<string, mixed>  $validated
     * @param  Collection<string, string>  $selectedFeatures
     * @return array<string, mixed>
     */
    private function viewFilters(array $validated, Collection $selectedFeatures): array
    {
        unset($validated['color'], $validated['size']);

        $validated['features'] = $selectedFeatures->all();

        return $validated;
    }

    /**
     * @return array<string, array{source: string, value: string}>
     */
    private function variantFeatureValues(ProductVariant $variant): array
    {
        $features = [];
        $options = $variant->options ?? [];

        if (! is_array($options)) {
            return [];
        }

        foreach ($options as $source => $value) {
            if ($source === 'attributes') {
                continue;
            }

            $this->appendVariantFeature($features, (string) $source, $value);
        }

        $attributes = $options['attributes'] ?? [];

        if (is_array($attributes)) {
            foreach ($attributes as $source => $value) {
                $this->appendVariantFeature($features, (string) $source, $value);
            }
        }

        return $features;
    }

    /**
     * @param  array<string, array{source: string, value: string}>  $features
     */
    private function appendVariantFeature(array &$features, string $source, mixed $value): void
    {
        if (blank($value) || is_array($value) || is_object($value)) {
            return;
        }

        $key = $this->normalizeFeatureKey($source);

        if ($key === null || array_key_exists($key, $features)) {
            return;
        }

        $features[$key] = [
            'source' => $source,
            'value' => trim((string) $value),
        ];
    }

    private function normalizeFeatureKey(string $key): ?string
    {
        $normalized = Str::of($key)
            ->lower()
            ->replaceMatches('/[^\pL\pN]+/u', '_')
            ->trim('_')
            ->toString();

        return $normalized !== '' ? $normalized : null;
    }

    private function featureLabel(string $key, string $source): string
    {
        if (isset(self::FEATURE_LABELS[$key])) {
            return self::FEATURE_LABELS[$key];
        }

        return Str::of($source)
            ->replace(['_', '-'], ' ')
            ->squish()
            ->headline()
            ->toString();
    }

    private function featurePlaceholder(string $key, string $source): string
    {
        if (isset(self::FEATURE_PLACEHOLDERS[$key])) {
            return self::FEATURE_PLACEHOLDERS[$key];
        }

        return 'همه '.$this->featureLabel($key, $source);
    }
}
