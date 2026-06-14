<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Support\Storefront\Seo;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class ShopController extends Controller
{
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
                    ['name' => $product->category->label, 'url' => route('storefront.categories.show', $product->category)],
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
            'sort' => ['nullable', 'string', 'in:recommended,price_low,price_high,newest'],
        ]);

        $query = Product::query()
            ->with('category')
            ->where('is_active', true)
            ->whereHas('category', fn (Builder $query) => $query->where('is_active', true));

        if ($category) {
            $query->where('product_category_id', $category->id);
        }

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

        match ($validated['sort'] ?? 'recommended') {
            'price_low' => $query->orderBy('price_value'),
            'price_high' => $query->orderByDesc('price_value'),
            'newest' => $query->latest(),
            default => $query->orderByDesc('is_featured')->orderBy('sort_order')->orderBy('title'),
        };

        $products = $query->paginate(12)->withQueryString();
        $categories = ProductCategory::query()
            ->where('is_active', true)
            ->withCount(['products as active_products_count' => fn ($query) => $query->where('is_active', true)])
            ->orderBy('sort_order')
            ->orderBy('label')
            ->get();

        $hasFilters = collect($validated)->filter()->isNotEmpty();
        $canonical = $category
            ? route('storefront.categories.show', $category)
            : route('storefront.shop');

        return view('storefront.shop.index', [
            'category' => $category,
            'categories' => $categories,
            'products' => $products,
            'filters' => $validated,
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
                    $category ? ['name' => $category->label, 'url' => route('storefront.categories.show', $category)] : null,
                ])),
                Seo::itemList($products->getCollection(), $canonical),
            ],
        ]);
    }
}
