@php
    $siteName = 'EtokBike';
    $title = $meta['title'] ?? $siteName;
    $description = $meta['description'] ?? 'فروشگاه دوچرخه، قطعات و لوازم جانبی EtokBike.';
    $canonical = $meta['canonical'] ?? url()->current();
    $robots = $meta['robots'] ?? 'index,follow';
    $image = $meta['image'] ?? asset('images/storefront/hero-shop.png');
    $ogTitle = $meta['ogTitle'] ?? $title;
    $ogDescription = $meta['ogDescription'] ?? $description;
    $schemas = collect($structuredData ?? [])->filter()->values();
    $desktopNavItems = [
        ['label' => 'خانه', 'url' => route('storefront.home'), 'active' => request()->routeIs('storefront.home')],
        ['label' => 'فروشگاه', 'url' => route('storefront.shop'), 'active' => request()->routeIs('storefront.shop', 'storefront.categories.show', 'storefront.products.show')],
        ['label' => 'خدمات', 'url' => route('storefront.services'), 'active' => request()->routeIs('storefront.services')],
        ['label' => 'برنامه‌ها', 'url' => route('storefront.events'), 'active' => request()->routeIs('storefront.events', 'storefront.events.show')],
        ['label' => 'پیام', 'url' => route('storefront.messages'), 'active' => request()->routeIs('storefront.messages')],
        ['label' => 'حساب', 'url' => route('storefront.account'), 'active' => request()->routeIs('storefront.account')],
    ];
    $mobileNavItems = [
        ['label' => 'خانه', 'url' => route('storefront.home'), 'active' => request()->routeIs('storefront.home')],
        ['label' => 'فروشگاه', 'url' => route('storefront.shop'), 'active' => request()->routeIs('storefront.shop', 'storefront.categories.show', 'storefront.products.show')],
        ['label' => 'خدمات', 'url' => route('storefront.services'), 'active' => request()->routeIs('storefront.services')],
        ['label' => 'برنامه‌ها', 'url' => route('storefront.events'), 'active' => request()->routeIs('storefront.events', 'storefront.events.show')],
        ['label' => 'حساب', 'url' => route('storefront.account'), 'active' => request()->routeIs('storefront.account')],
    ];
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
        <meta property="og:title" content="{{ $ogTitle }}">
        <meta property="og:description" content="{{ $ogDescription }}">
        <meta property="og:url" content="{{ $canonical }}">
        <meta property="og:image" content="{{ $image }}">
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="{{ $ogTitle }}">
        <meta name="twitter:description" content="{{ $ogDescription }}">
        <meta name="twitter:image" content="{{ $image }}">

        <title>{{ $title }}</title>

        @fonts

        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @else
            <style>
                body { margin: 0; background: #FAF8F5; color: #101114; font-family: Tahoma, ui-sans-serif, system-ui, sans-serif; }
                a { color: inherit; }
                img { max-width: 100%; height: auto; }
            </style>
        @endif

        @stack('head')

        @foreach ($schemas as $schema)
            <script type="application/ld+json">@json($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)</script>
        @endforeach
    </head>
    <body class="storefront-shell flex min-h-dvh flex-col overflow-x-hidden bg-surface-page pb-20 text-ink antialiased selection:bg-brand selection:text-white md:pb-0">
        <a href="#content" class="sr-only focus:not-sr-only focus:fixed focus:top-3 focus:right-3 focus:z-50 focus:rounded-xl focus:bg-surface focus:px-4 focus:py-2 focus:text-sm focus:font-semibold focus:text-ink">
            رفتن به محتوا
        </a>

        <header class="sticky top-0 z-40 border-b border-border/80 bg-surface-page/95 backdrop-blur">
            <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-4 sm:px-6 lg:px-8">
                <a href="{{ route('storefront.home') }}" class="flex items-center gap-3" aria-label="EtokBike">
                    <span class="grid size-10 place-items-center rounded-xl bg-brand text-sm font-bold text-white shadow-sm shadow-red-900/10">ET</span>
                    <span class="text-lg font-semibold tracking-normal text-ink">EtokBike</span>
                </a>

                <nav class="hidden items-center gap-6 text-sm font-medium text-muted md:flex" aria-label="Main navigation">
                    @foreach ($desktopNavItems as $item)
                        <a href="{{ $item['url'] }}" class="transition hover:text-brand @if($item['active']) text-brand @endif">{{ $item['label'] }}</a>
                    @endforeach
                </nav>

                <a href="{{ route('storefront.cart.show') }}" class="inline-flex min-h-10 items-center gap-2 rounded-xl border border-border bg-surface px-3 py-2 text-sm font-semibold text-ink shadow-sm transition hover:border-brand hover:text-brand">
                    <span>سبد خرید</span>
                    <span class="grid min-w-6 place-items-center rounded-lg bg-brand px-2 py-0.5 text-xs text-white">{{ $cartCount ?? 0 }}</span>
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

        <main id="content" class="flex-1">
            @yield('content')
        </main>

        <footer class="mt-auto border-t border-border bg-surface">
            <div class="mx-auto grid max-w-7xl gap-8 px-4 py-10 text-sm text-muted sm:px-6 md:grid-cols-3 lg:px-8">
                <div>
                    <p class="text-base font-semibold text-ink">EtokBike</p>
                    <p class="mt-3 leading-7">فروشگاه دوچرخه، قطعات مصرفی، لوازم جانبی و سرویس تخصصی.</p>
                </div>
                <div>
                    <p class="font-semibold text-ink">خرید</p>
                    <div class="mt-3 grid gap-2">
                        <a class="transition hover:text-brand" href="{{ route('storefront.shop') }}">همه محصولات</a>
                        <a class="transition hover:text-brand" href="{{ route('storefront.cart.show') }}">سبد خرید</a>
                        <a class="transition hover:text-brand" href="{{ route('storefront.account') }}">پیگیری سفارش</a>
                    </div>
                </div>
                <div>
                    <p class="font-semibold text-ink">خدمات مشتری</p>
                    <div class="mt-3 grid gap-2">
                        <a class="transition hover:text-brand" href="{{ route('storefront.services') }}">رزرو خدمات</a>
                        <a class="transition hover:text-brand" href="{{ route('storefront.events') }}">برنامه‌ها</a>
                        <a class="transition hover:text-brand" href="{{ route('storefront.messages') }}">ارسال پیام</a>
                        <a class="transition hover:text-brand" href="{{ route('storefront.sitemap') }}">نقشه سایت</a>
                    </div>
                </div>
            </div>
        </footer>

        <nav class="fixed inset-x-0 bottom-0 z-50 border-t border-border bg-surface/95 px-2 pb-[max(env(safe-area-inset-bottom),0.5rem)] pt-2 shadow-[0_-16px_32px_rgba(16,17,20,0.08)] backdrop-blur md:hidden" aria-label="Mobile navigation">
            <div class="mx-auto grid max-w-md grid-cols-5 gap-1 text-center text-[11px] font-semibold">
                @foreach ($mobileNavItems as $item)
                    <a
                        href="{{ $item['url'] }}"
                        class="flex min-h-12 items-center justify-center rounded-xl px-1 transition @if($item['active']) bg-brand-soft text-brand @else text-muted hover:bg-surface-alt hover:text-ink @endif"
                        @if($item['active']) aria-current="page" @endif
                    >
                        {{ $item['label'] }}
                    </a>
                @endforeach
            </div>
        </nav>

        @stack('scripts')
    </body>
</html>
