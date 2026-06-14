<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\ContentPage;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Http\Response;

class SeoController extends Controller
{
    public function sitemap(): Response
    {
        $products = Product::query()
            ->where('is_active', true)
            ->where('include_in_sitemap', true)
            ->whereHas('category', fn ($query) => $query->where('is_active', true))
            ->latest('updated_at')
            ->get();

        $categories = ProductCategory::query()
            ->where('is_active', true)
            ->where('include_in_sitemap', true)
            ->latest('updated_at')
            ->get();

        $pages = ContentPage::query()
            ->where('is_active', true)
            ->where('include_in_sitemap', true)
            ->latest('updated_at')
            ->get();

        return response()
            ->view('storefront.seo.sitemap', [
                'products' => $products,
                'categories' => $categories,
                'pages' => $pages,
            ])
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    public function robots(): Response
    {
        return response(implode("\n", [
            'User-agent: *',
            'Disallow: /admin',
            'Disallow: /cart',
            'Disallow: /checkout',
            'Sitemap: '.route('storefront.sitemap'),
            '',
        ]), 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }
}
