<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Support\Mobile\ImageUrl;
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

        $image = ImageUrl::resolve($product->image_url);

        return view('storefront.shop.show', [
            'product' => $product,
            'relatedProducts' => $relatedProducts,
            'meta' => [
                'title' => $product->title.' | EtokBike',
                'description' => Seo::description($product->description, $product->subtitle),
                'canonical' => route('storefront.products.show', $product),
                'image' => Seo::image($image),
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
                'description' => $category
                    ? 'خرید محصولات دسته '.$category->label.' از فروشگاه EtokBike با موجودی، قیمت و ثبت سفارش آنلاین.'
                    : 'خرید دوچرخه، قطعات و لوازم جانبی از فروشگاه EtokBike با فیلتر موجودی، قیمت و دسته‌بندی.',
                'canonical' => $canonical,
                'robots' => $hasFilters ? 'noindex,follow' : 'index,follow',
                'image' => asset('images/storefront/hero-shop.png'),
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
