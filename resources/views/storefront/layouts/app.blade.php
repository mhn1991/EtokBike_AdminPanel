@php
    $siteName = 'EtokBike';
    $title = $meta['title'] ?? $siteName;
    $description = $meta['description'] ?? 'فروشگاه دوچرخه، قطعات و لوازم جانبی EtokBike.';
    $canonical = $meta['canonical'] ?? url()->current();
    $robots = $meta['robots'] ?? 'index,follow';
    $image = $meta['image'] ?? asset('images/storefront/hero-shop.png');
    $schemas = collect($structuredData ?? [])->filter()->values();
@endphp
<!DOCTYPE html>
<html lang="fa" dir="rtl">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="robots" content="{{ $robots }}">
        <meta name="description" content="{{ $description }}">
        <link rel="canonical" href="{{ $canonical }}">
        <link rel="sitemap" type="application/xml" href="{{ route('storefront.sitemap') }}">

        <meta property="og:type" content="@yield('og_type', 'website')">
        <meta property="og:site_name" content="{{ $siteName }}">
        <meta property="og:title" content="{{ $title }}">
        <meta property="og:description" content="{{ $description }}">
        <meta property="og:url" content="{{ $canonical }}">
        <meta property="og:image" content="{{ $image }}">
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="{{ $title }}">
        <meta name="twitter:description" content="{{ $description }}">
        <meta name="twitter:image" content="{{ $image }}">

        <title>{{ $title }}</title>

        @fonts

        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @else
            <style>
                body { margin: 0; background: #f6f3ef; color: #171717; font-family: ui-sans-serif, system-ui, sans-serif; }
                a { color: inherit; }
                img { max-width: 100%; height: auto; }
            </style>
        @endif

        @stack('head')

        @foreach ($schemas as $schema)
            <script type="application/ld+json">@json($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)</script>
        @endforeach
    </head>
    <body class="bg-[#f6f3ef] text-neutral-950 antialiased selection:bg-red-700 selection:text-white">
        <a href="#content" class="sr-only focus:not-sr-only focus:fixed focus:top-3 focus:right-3 focus:z-50 focus:rounded-md focus:bg-white focus:px-4 focus:py-2 focus:text-sm focus:font-semibold focus:text-neutral-950">
            رفتن به محتوا
        </a>

        <header class="sticky top-0 z-40 border-b border-neutral-200/80 bg-[#f6f3ef]/95 backdrop-blur">
            <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-4 sm:px-6 lg:px-8">
                <a href="{{ route('storefront.home') }}" class="flex items-center gap-3" aria-label="EtokBike">
                    <span class="grid size-10 place-items-center rounded-md bg-neutral-950 text-sm font-bold text-white">ET</span>
                    <span class="text-lg font-semibold tracking-normal">EtokBike</span>
                </a>

                <nav class="hidden items-center gap-6 text-sm font-medium text-neutral-700 md:flex" aria-label="Main navigation">
                    <a href="{{ route('storefront.home') }}" class="hover:text-red-700 @if(request()->routeIs('storefront.home')) text-red-700 @endif">خانه</a>
                    <a href="{{ route('storefront.shop') }}" class="hover:text-red-700 @if(request()->routeIs('storefront.shop', 'storefront.categories.show', 'storefront.products.show')) text-red-700 @endif">فروشگاه</a>
                </nav>

                <a href="{{ route('storefront.cart.show') }}" class="inline-flex min-h-10 items-center gap-2 rounded-md border border-neutral-300 bg-white px-3 py-2 text-sm font-semibold text-neutral-950 hover:border-red-700 hover:text-red-700">
                    <span>سبد خرید</span>
                    <span class="grid min-w-6 place-items-center rounded-md bg-neutral-950 px-2 py-0.5 text-xs text-white">{{ $cartCount ?? 0 }}</span>
                </a>
            </div>
        </header>

        @if (session('status'))
            <div class="border-b border-emerald-200 bg-emerald-50 text-emerald-900">
                <div class="mx-auto max-w-7xl px-4 py-3 text-sm font-medium sm:px-6 lg:px-8">
                    {{ session('status') }}
                </div>
            </div>
        @endif

        <main id="content">
            @yield('content')
        </main>

        <footer class="border-t border-neutral-200 bg-white">
            <div class="mx-auto grid max-w-7xl gap-8 px-4 py-10 text-sm text-neutral-700 sm:px-6 md:grid-cols-3 lg:px-8">
                <div>
                    <p class="text-base font-semibold text-neutral-950">EtokBike</p>
                    <p class="mt-3 leading-7">فروشگاه دوچرخه، قطعات مصرفی، لوازم جانبی و سرویس تخصصی.</p>
                </div>
                <div>
                    <p class="font-semibold text-neutral-950">خرید</p>
                    <div class="mt-3 grid gap-2">
                        <a class="hover:text-red-700" href="{{ route('storefront.shop') }}">همه محصولات</a>
                        <a class="hover:text-red-700" href="{{ route('storefront.cart.show') }}">سبد خرید</a>
                    </div>
                </div>
                <div>
                    <p class="font-semibold text-neutral-950">اطلاعات سایت</p>
                    <div class="mt-3 grid gap-2">
                        <a class="hover:text-red-700" href="{{ route('storefront.sitemap') }}">نقشه سایت</a>
                    </div>
                </div>
            </div>
        </footer>
    </body>
</html>
