<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\ContentPage;
use App\Support\Storefront\Seo;
use Illuminate\Contracts\View\View;

class PageController extends Controller
{
    public function show(ContentPage $page): View
    {
        abort_unless($page->is_active, 404);

        return view('storefront.pages.show', [
            'page' => $page,
            'meta' => [
                'title' => $page->seo_title ?: $page->title.' | '.Seo::siteName(),
                'description' => Seo::description($page->seo_description, $page->excerpt ?: $page->title),
                'canonical' => $page->canonical_url ?: route('storefront.pages.show', $page),
                'robots' => $page->robots,
                'image' => Seo::image($page->og_image),
                'ogTitle' => $page->og_title ?: ($page->seo_title ?: $page->title.' | '.Seo::siteName()),
                'ogDescription' => $page->og_description ?: Seo::description($page->seo_description, $page->excerpt ?: $page->title),
            ],
            'structuredData' => [
                Seo::breadcrumbs([
                    ['name' => Seo::siteName(), 'url' => route('storefront.home')],
                    ['name' => $page->title, 'url' => route('storefront.pages.show', $page)],
                ]),
            ],
        ]);
    }
}
