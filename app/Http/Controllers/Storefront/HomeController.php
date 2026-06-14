<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\StoreProfile;
use App\Support\Storefront\Seo;
use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $categories = ProductCategory::query()
            ->where('is_active', true)
            ->withCount(['products as active_products_count' => fn ($query) => $query->where('is_active', true)])
            ->orderBy('sort_order')
            ->orderBy('label')
            ->get();

        $featuredProducts = Product::query()
            ->with('category')
            ->where('is_active', true)
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->limit(6)
            ->get();

        return view('storefront.home', [
            'categories' => $categories,
            'featuredProducts' => $featuredProducts,
            'storeProfile' => StoreProfile::query()->where('is_active', true)->first(),
            'meta' => [
                'title' => Seo::defaultTitle('EtokBike | فروشگاه دوچرخه و لوازم دوچرخه'),
                'description' => Seo::defaultDescription('خرید دوچرخه شهری، کوهستان، جاده، قطعات و لوازم جانبی از فروشگاه EtokBike با موجودی و سفارش مستقیم.'),
                'canonical' => route('storefront.home'),
                'image' => Seo::defaultImage(),
            ],
            'structuredData' => [
                Seo::organization(),
                Seo::itemList($featuredProducts, route('storefront.home')),
            ],
        ]);
    }
}
