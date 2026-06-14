{!! '<'.'?xml version="1.0" encoding="UTF-8"?'.'>' !!}
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <url>
        <loc>{{ route('storefront.home') }}</loc>
        <lastmod>{{ now()->toAtomString() }}</lastmod>
        <changefreq>daily</changefreq>
        <priority>1.0</priority>
    </url>
    <url>
        <loc>{{ route('storefront.shop') }}</loc>
        <lastmod>{{ now()->toAtomString() }}</lastmod>
        <changefreq>daily</changefreq>
        <priority>0.9</priority>
    </url>
    @foreach ($categories as $category)
        <url>
            <loc>{{ route('storefront.categories.show', $category) }}</loc>
            <lastmod>{{ $category->updated_at->toAtomString() }}</lastmod>
            <changefreq>{{ $category->sitemap_change_frequency }}</changefreq>
            <priority>{{ $category->sitemap_priority }}</priority>
        </url>
    @endforeach
    @foreach ($products as $product)
        <url>
            <loc>{{ route('storefront.products.show', $product) }}</loc>
            <lastmod>{{ $product->updated_at->toAtomString() }}</lastmod>
            <changefreq>{{ $product->sitemap_change_frequency }}</changefreq>
            <priority>{{ $product->sitemap_priority }}</priority>
        </url>
    @endforeach
    @foreach ($pages as $page)
        <url>
            <loc>{{ route('storefront.pages.show', $page) }}</loc>
            <lastmod>{{ $page->updated_at->toAtomString() }}</lastmod>
            <changefreq>{{ $page->sitemap_change_frequency }}</changefreq>
            <priority>{{ $page->sitemap_priority }}</priority>
        </url>
    @endforeach
</urlset>
