@extends('storefront.layouts.app')

@php
    $listingRoute = $category ? route('storefront.categories.show', $category) : route('storefront.shop');
    $availabilityLabels = [
        'in_stock' => 'موجود',
        'low_stock' => 'موجودی محدود',
        'orderable' => 'قابل سفارش',
        'out_of_stock' => 'ناموجود',
    ];
    $priceLabels = [
        'under_2m' => 'زیر ۲ میلیون',
        '2m_20m' => '۲ تا ۲۰ میلیون',
        'over_20m' => 'بالای ۲۰ میلیون',
    ];
    $sortLabels = [
        'recommended' => 'پیشنهادی',
        'price_low' => 'ارزان‌ترین',
        'price_high' => 'گران‌ترین',
        'newest' => 'جدیدترین',
    ];
    $activeFilterCount = collect([
        $filters['q'] ?? null,
        $filters['availability'] ?? null,
        $filters['price'] ?? null,
        (($filters['sort'] ?? 'recommended') !== 'recommended') ? ($filters['sort'] ?? null) : null,
    ])->filter(fn ($value) => ! blank($value))->count();
@endphp

@push('head')
    <style>
        .storefront-mobile-shop-tools { display: grid; }
        .storefront-mobile-category-link { white-space: nowrap; }
        .storefront-filter-summary::-webkit-details-marker { display: none; }
        .storefront-shop-scrollbar { scrollbar-width: none; }
        .storefront-shop-scrollbar::-webkit-scrollbar { display: none; }

        @media (min-width: 1024px) {
            .storefront-mobile-shop-tools { display: none; }
        }
    </style>
@endpush

@section('content')
    <section class="overflow-hidden border-b border-border bg-surface">
        <div class="mx-auto grid max-w-7xl gap-6 px-4 py-7 sm:px-6 sm:py-9 lg:px-8">
            @include('storefront.partials.breadcrumbs', ['items' => array_filter([
                ['name' => 'EtokBike', 'url' => route('storefront.home')],
                ['name' => 'فروشگاه', 'url' => route('storefront.shop')],
                $category ? ['name' => $category->label, 'url' => route('storefront.categories.show', $category)] : null,
            ])])

            <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_420px] lg:items-end">
                <div>
                    <p class="inline-flex rounded-full bg-brand-soft px-3 py-1 text-xs font-semibold text-brand">فروشگاه تخصصی دوچرخه</p>
                    <h1 class="mt-4 text-3xl font-bold leading-tight tracking-normal text-ink sm:text-4xl">
                        {{ $category ? $category->label : 'فروشگاه دوچرخه EtokBike' }}
                    </h1>
                    <p class="mt-3 max-w-2xl leading-7 text-muted">
                        دوچرخه، قطعات و لوازم جانبی را با قیمت، موجودی و دسته‌بندی روشن‌تر پیدا کنید.
                    </p>
                    <div class="mt-5 flex flex-wrap gap-2 text-xs font-semibold text-muted">
                        <span class="rounded-full border border-border bg-surface-alt px-3 py-1.5">{{ number_format($products->total()) }} محصول</span>
                        <span class="rounded-full border border-border bg-surface-alt px-3 py-1.5">{{ number_format($categories->count()) }} دسته</span>
                        <span class="rounded-full border border-emerald-100 bg-emerald-50 px-3 py-1.5 text-emerald-700">موجودی قابل پیگیری</span>
                    </div>
                </div>

                <form action="{{ $listingRoute }}" method="GET" class="grid gap-2 rounded-2xl border border-border bg-surface-alt p-2 shadow-sm sm:grid-cols-[1fr_auto]">
                    <label for="shop-search" class="sr-only">جستجوی محصول</label>
                    <input
                        id="shop-search"
                        name="q"
                        value="{{ $filters['q'] ?? '' }}"
                        type="search"
                        placeholder="نام دوچرخه، قطعه یا لوازم جانبی"
                        class="min-h-12 min-w-0 rounded-xl border border-transparent bg-surface px-4 text-sm text-ink outline-none placeholder:text-muted/70 focus:border-brand focus:ring-2 focus:ring-brand/20"
                    >
                    <button type="submit" class="min-h-12 rounded-xl bg-brand px-5 text-sm font-semibold text-white shadow-sm shadow-red-900/15 transition hover:bg-brand-hover focus:outline-none focus:ring-2 focus:ring-brand focus:ring-offset-2 focus:ring-offset-surface-alt">جستجو</button>
                </form>
            </div>
        </div>
    </section>

    <section class="bg-surface-page py-6 pb-12 sm:py-8">
        <div class="mx-auto grid max-w-7xl gap-6 px-4 sm:px-6 lg:grid-cols-[280px_minmax(0,1fr)] lg:px-8">
            <aside class="hidden lg:sticky lg:top-24 lg:block lg:self-start">
                <div class="rounded-2xl border border-border bg-surface p-5 shadow-sm">
                    <div class="flex items-center justify-between gap-3">
                        <h2 class="text-base font-semibold text-ink">دسته‌بندی</h2>
                        <span class="text-xs font-medium text-muted">{{ number_format($categories->count()) }} دسته</span>
                    </div>
                    <nav class="mt-4 grid gap-2" aria-label="Product categories">
                        <a href="{{ route('storefront.shop') }}" class="rounded-xl px-3 py-2.5 text-sm font-semibold transition @if(! $category) bg-brand text-white shadow-sm shadow-red-900/10 @else text-ink hover:bg-brand-soft hover:text-brand @endif">
                            همه محصولات
                        </a>
                        @foreach ($categories as $item)
                            <a href="{{ route('storefront.categories.show', $item) }}" class="flex items-center justify-between gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold transition @if($category?->id === $item->id) bg-brand text-white shadow-sm shadow-red-900/10 @else text-ink hover:bg-brand-soft hover:text-brand @endif">
                                <span>{{ $item->label }}</span>
                                <span class="text-xs opacity-75">{{ $item->active_products_count }}</span>
                            </a>
                        @endforeach
                    </nav>

                    <form action="{{ $listingRoute }}" method="GET" class="mt-5 grid gap-4 border-t border-border pt-5">
                        @if (! blank($filters['q'] ?? null))
                            <input type="hidden" name="q" value="{{ $filters['q'] }}">
                        @endif
                        <label class="grid gap-2 text-sm font-semibold text-ink">
                            موجودی
                            <select name="availability" class="min-h-11 rounded-xl border border-border bg-surface px-3 text-sm font-medium text-ink outline-none focus:border-brand focus:ring-2 focus:ring-brand/20">
                                <option value="">همه وضعیت‌ها</option>
                                @foreach ($availabilityLabels as $key => $label)
                                    <option value="{{ $key }}" @selected(($filters['availability'] ?? '') === $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label class="grid gap-2 text-sm font-semibold text-ink">
                            قیمت
                            <select name="price" class="min-h-11 rounded-xl border border-border bg-surface px-3 text-sm font-medium text-ink outline-none focus:border-brand focus:ring-2 focus:ring-brand/20">
                                <option value="">همه قیمت‌ها</option>
                                @foreach ($priceLabels as $key => $label)
                                    <option value="{{ $key }}" @selected(($filters['price'] ?? '') === $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label class="grid gap-2 text-sm font-semibold text-ink">
                            مرتب‌سازی
                            <select name="sort" class="min-h-11 rounded-xl border border-border bg-surface px-3 text-sm font-medium text-ink outline-none focus:border-brand focus:ring-2 focus:ring-brand/20">
                                @foreach ($sortLabels as $key => $label)
                                    <option value="{{ $key }}" @selected(($filters['sort'] ?? 'recommended') === $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </label>
                        <button type="submit" class="min-h-11 rounded-xl bg-brand px-4 text-sm font-semibold text-white shadow-sm shadow-red-900/15 transition hover:bg-brand-hover focus:outline-none focus:ring-2 focus:ring-brand focus:ring-offset-2 focus:ring-offset-surface">اعمال فیلتر</button>
                        <a href="{{ $listingRoute }}" class="text-center text-sm font-semibold text-brand transition hover:text-brand-hover">حذف فیلترها</a>
                    </form>
                </div>
            </aside>

            <div class="min-w-0">
                <div class="mb-5 rounded-2xl border border-border bg-surface p-4 shadow-sm">
                    <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-center">
                        <div>
                            <p class="text-sm font-semibold text-ink">{{ number_format($products->total()) }} محصول پیدا شد</p>
                            @if (! blank($filters['q'] ?? null))
                                <p class="mt-1 text-sm text-muted">نتیجه برای: <span class="font-semibold text-ink">{{ $filters['q'] }}</span></p>
                            @endif
                        </div>
                        @if ($activeFilterCount > 0)
                            <a href="{{ $listingRoute }}" class="inline-flex min-h-10 items-center justify-center rounded-xl border border-border px-4 text-sm font-semibold text-ink transition hover:border-brand hover:text-brand">
                                حذف فیلترها
                            </a>
                        @endif
                    </div>

                    @if ($activeFilterCount > 0 || $category)
                        <div class="mt-3 flex flex-wrap gap-2 text-xs font-semibold">
                            @if ($category)
                                <span class="rounded-full bg-brand-soft px-3 py-1.5 text-brand">{{ $category->label }}</span>
                            @endif
                            @if (! blank($filters['q'] ?? null))
                                <span class="rounded-full bg-surface-alt px-3 py-1.5 text-muted">جستجو: {{ $filters['q'] }}</span>
                            @endif
                            @if (! blank($filters['availability'] ?? null))
                                <span class="rounded-full bg-surface-alt px-3 py-1.5 text-muted">موجودی: {{ $availabilityLabels[$filters['availability']] ?? $filters['availability'] }}</span>
                            @endif
                            @if (! blank($filters['price'] ?? null))
                                <span class="rounded-full bg-surface-alt px-3 py-1.5 text-muted">قیمت: {{ $priceLabels[$filters['price']] ?? $filters['price'] }}</span>
                            @endif
                            @if (($filters['sort'] ?? 'recommended') !== 'recommended')
                                <span class="rounded-full bg-surface-alt px-3 py-1.5 text-muted">مرتب‌سازی: {{ $sortLabels[$filters['sort']] ?? $filters['sort'] }}</span>
                            @endif
                        </div>
                    @endif
                </div>

                <div class="storefront-mobile-shop-tools mb-5 gap-3">
                    <div class="-mx-4 overflow-x-auto px-4 storefront-shop-scrollbar">
                        <div class="flex min-w-max gap-2 pb-1">
                            <a href="{{ route('storefront.shop') }}" class="storefront-mobile-category-link inline-flex min-h-11 items-center rounded-full border px-4 text-sm font-semibold transition @if(! $category) border-brand bg-brand text-white @else border-border bg-surface text-ink @endif">
                                همه محصولات
                            </a>
                            @foreach ($categories as $item)
                                <a href="{{ route('storefront.categories.show', $item) }}" class="storefront-mobile-category-link inline-flex min-h-11 items-center rounded-full border px-4 text-sm font-semibold transition @if($category?->id === $item->id) border-brand bg-brand text-white @else border-border bg-surface text-ink @endif">
                                    {{ $item->label }}
                                    <span class="mr-2 text-xs opacity-70">{{ $item->active_products_count }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>

                    <details class="rounded-2xl border border-border bg-surface shadow-sm">
                        <summary class="storefront-filter-summary flex min-h-13 cursor-pointer list-none items-center justify-between px-4 text-sm font-semibold text-ink">
                            <span>فیلتر و مرتب‌سازی</span>
                            @if ($activeFilterCount > 0)
                                <span class="rounded-full bg-brand-soft px-2.5 py-1 text-xs text-brand">{{ $activeFilterCount }} فعال</span>
                            @endif
                        </summary>
                        <form action="{{ $listingRoute }}" method="GET" class="grid gap-4 border-t border-border p-4">
                            @if (! blank($filters['q'] ?? null))
                                <input type="hidden" name="q" value="{{ $filters['q'] }}">
                            @endif
                            <label class="grid gap-2 text-sm font-semibold text-ink">
                                موجودی
                                <select name="availability" class="min-h-11 rounded-xl border border-border bg-surface px-3 text-sm font-medium text-ink outline-none focus:border-brand focus:ring-2 focus:ring-brand/20">
                                    <option value="">همه وضعیت‌ها</option>
                                    @foreach ($availabilityLabels as $key => $label)
                                        <option value="{{ $key }}" @selected(($filters['availability'] ?? '') === $key)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </label>
                            <label class="grid gap-2 text-sm font-semibold text-ink">
                                قیمت
                                <select name="price" class="min-h-11 rounded-xl border border-border bg-surface px-3 text-sm font-medium text-ink outline-none focus:border-brand focus:ring-2 focus:ring-brand/20">
                                    <option value="">همه قیمت‌ها</option>
                                    @foreach ($priceLabels as $key => $label)
                                        <option value="{{ $key }}" @selected(($filters['price'] ?? '') === $key)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </label>
                            <label class="grid gap-2 text-sm font-semibold text-ink">
                                مرتب‌سازی
                                <select name="sort" class="min-h-11 rounded-xl border border-border bg-surface px-3 text-sm font-medium text-ink outline-none focus:border-brand focus:ring-2 focus:ring-brand/20">
                                    @foreach ($sortLabels as $key => $label)
                                        <option value="{{ $key }}" @selected(($filters['sort'] ?? 'recommended') === $key)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </label>
                            <button type="submit" class="min-h-11 rounded-xl bg-brand px-4 text-sm font-semibold text-white shadow-sm shadow-red-900/15 transition hover:bg-brand-hover focus:outline-none focus:ring-2 focus:ring-brand focus:ring-offset-2 focus:ring-offset-surface">اعمال فیلتر</button>
                            <a href="{{ $listingRoute }}" class="text-center text-sm font-semibold text-brand transition hover:text-brand-hover">حذف فیلترها</a>
                        </form>
                    </details>
                </div>

                @if ($products->isEmpty())
                    <div class="rounded-2xl border border-border bg-surface px-5 py-14 text-center shadow-sm">
                        <p class="text-sm font-semibold text-brand">نتیجه‌ای پیدا نشد</p>
                        <h2 class="mt-3 text-2xl font-bold text-ink">محصولی با این فیلترها نداریم</h2>
                        <p class="mx-auto mt-3 max-w-md leading-7 text-muted">فیلترها را سبک‌تر کنید، همه محصولات را ببینید یا برای انتخاب قطعه مناسب پیام بفرستید.</p>
                        <div class="mt-6 flex flex-col justify-center gap-3 sm:flex-row">
                            <a href="{{ $listingRoute }}" class="inline-flex min-h-11 items-center justify-center rounded-xl bg-brand px-5 text-sm font-semibold text-white transition hover:bg-brand-hover">حذف فیلترها</a>
                            <a href="{{ route('storefront.messages') }}" class="inline-flex min-h-11 items-center justify-center rounded-xl border border-border bg-surface px-5 text-sm font-semibold text-ink transition hover:border-brand hover:text-brand">ارسال پیام</a>
                        </div>
                    </div>
                @else
                    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4">
                        @foreach ($products as $product)
                            @include('storefront.partials.product-card', ['product' => $product])
                        @endforeach
                    </div>

                    <div class="mt-8">
                        {{ $products->links() }}
                    </div>
                @endif
            </div>
        </div>
    </section>
@endsection
