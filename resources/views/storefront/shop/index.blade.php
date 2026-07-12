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
    $selectedFeatures = collect($filters['features'] ?? [])->filter(fn ($value) => ! blank($value));
    $featureLabels = collect($featureFilters['features'] ?? [])->mapWithKeys(fn ($feature) => [$feature['key'] => $feature['label']]);
    $activeFilterCount = collect([
        $filters['q'] ?? null,
        $filters['availability'] ?? null,
        $filters['price'] ?? null,
        $filters['min_price'] ?? null,
        $filters['max_price'] ?? null,
        (($filters['sort'] ?? 'recommended') !== 'recommended') ? ($filters['sort'] ?? null) : null,
    ])->filter(fn ($value) => ! blank($value))->count() + $selectedFeatures->count();
    $advancedFilterOpen = $selectedFeatures->isNotEmpty()
        || ! blank($filters['price'] ?? null)
        || ! blank($filters['min_price'] ?? null)
        || ! blank($filters['max_price'] ?? null);
    $activeCategoryIds = $category?->breadcrumbCategories()->pluck('id')->all() ?? [];
    $activeRootCategoryId = $category?->breadcrumbCategories()->first()?->id;
    $categoriesByParent = $categories->groupBy(fn ($item) => (int) ($item->parent_id ?? 0));
    $rootCategories = $categoriesByParent->get(0, collect());
@endphp

@push('head')
    <style>
        .storefront-mobile-category-link { white-space: nowrap; }
        .storefront-shop-scrollbar { scrollbar-width: none; }
        .storefront-shop-scrollbar::-webkit-scrollbar { display: none; }
        .storefront-shell .storefront-shop-container { width: 100%; max-width: none !important; }
        .storefront-category-menu > summary { list-style: none; }
        .storefront-category-menu > summary::-webkit-details-marker { display: none; }
        .storefront-category-menu-panel { display: none; }
        .storefront-category-menu[open] > .storefront-category-menu-panel { display: grid; }
        @media (hover: hover) and (pointer: fine) {
            .storefront-category-menu:hover > .storefront-category-menu-panel,
            .storefront-category-menu:focus-within > .storefront-category-menu-panel { display: grid; }
        }
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('[data-shop-filter-form]').forEach((form) => {
                form.querySelectorAll('[data-shop-filter-control]').forEach((control) => {
                    control.addEventListener('change', () => {
                        if (typeof form.requestSubmit === 'function') {
                            form.requestSubmit();
                            return;
                        }

                        form.submit();
                    });
                });
            });

            document.querySelectorAll('[data-shop-filter-toggle]').forEach((toggle) => {
                const panel = document.getElementById(toggle.getAttribute('aria-controls'));
                const label = toggle.querySelector('[data-shop-filter-toggle-label]');

                if (! panel) {
                    return;
                }

                const setOpen = (open) => {
                    panel.hidden = ! open;
                    toggle.setAttribute('aria-expanded', open ? 'true' : 'false');

                    if (label) {
                        label.textContent = open ? 'بستن فیلترها' : 'فیلترهای بیشتر';
                    }
                };

                setOpen(! panel.hidden);

                toggle.addEventListener('click', () => setOpen(panel.hidden));
            });
        });
    </script>
@endpush

@section('content')
    <section class="border-b border-border bg-surface-page">
        <div class="storefront-shop-container mx-auto grid max-w-7xl gap-4 px-4 py-4 sm:px-6 sm:py-5 lg:px-8">
            @include('storefront.partials.breadcrumbs', ['items' => array_filter([
                ['name' => 'EtokBike', 'url' => route('storefront.home')],
                ['name' => 'فروشگاه', 'url' => route('storefront.shop')],
                $category ? ['name' => $category->label, 'url' => route('storefront.categories.show', $category)] : null,
            ])])

            <form action="{{ $listingRoute }}" method="GET" class="grid gap-4 rounded-3xl border border-border/80 bg-surface p-3 shadow-sm sm:p-5" data-shop-filter-form>
                <h1 class="sr-only">{{ $category ? $category->label : 'فروشگاه دوچرخه EtokBike' }}</h1>

                <div class="grid gap-2 rounded-2xl border border-border bg-surface-alt p-2 shadow-sm sm:grid-cols-[minmax(0,1fr)_auto]">
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
                </div>

                <div class="grid gap-3 border-t border-border/70 pt-3">
                    <div class="flex flex-col gap-3 xl:flex-row xl:items-center xl:justify-between">
                        <div class="flex min-w-0 flex-wrap items-center gap-2">
                            <p class="rounded-full bg-surface-alt px-3 py-1.5 text-xs font-semibold text-ink">{{ number_format($products->total()) }} محصول</p>
                            @if (! blank($filters['q'] ?? null))
                                <span class="rounded-full bg-brand-soft px-3 py-1.5 text-xs font-semibold text-brand">جستجو: {{ $filters['q'] }}</span>
                            @endif
                            @if ($activeFilterCount > 0)
                                <span class="rounded-full bg-brand-soft px-3 py-1.5 text-xs font-semibold text-brand">{{ $activeFilterCount }} فیلتر فعال</span>
                            @endif
                        </div>

                        <div class="grid grid-cols-2 gap-2 sm:flex sm:flex-wrap sm:items-center sm:justify-end">
                            <label class="sr-only" for="shop-availability">وضعیت موجودی</label>
                            <select id="shop-availability" name="availability" class="min-h-10 rounded-full border border-border bg-surface px-3 text-xs font-semibold text-ink outline-none transition hover:border-brand focus:border-brand focus:ring-2 focus:ring-brand/20 sm:min-w-36" data-shop-filter-control>
                                <option value="">همه وضعیت‌ها</option>
                                @foreach ($availabilityLabels as $key => $label)
                                    <option value="{{ $key }}" @selected(($filters['availability'] ?? '') === $key)>{{ $label }}</option>
                                @endforeach
                            </select>

                            <label class="sr-only" for="shop-sort">مرتب‌سازی</label>
                            <select id="shop-sort" name="sort" class="min-h-10 rounded-full border border-border bg-surface px-3 text-xs font-semibold text-ink outline-none transition hover:border-brand focus:border-brand focus:ring-2 focus:ring-brand/20 sm:min-w-36" data-shop-filter-control>
                                @foreach ($sortLabels as $key => $label)
                                    <option value="{{ $key }}" @selected(($filters['sort'] ?? 'recommended') === $key)>{{ $label }}</option>
                                @endforeach
                            </select>

                            <button
                                type="button"
                                class="col-span-2 inline-flex min-h-10 items-center justify-center gap-2 rounded-full border border-brand bg-brand-soft px-4 text-xs font-bold text-brand transition hover:bg-white focus:outline-none focus:ring-2 focus:ring-brand focus:ring-offset-2 focus:ring-offset-surface sm:col-span-1"
                                data-shop-filter-toggle
                                aria-controls="shop-advanced-filters"
                                aria-expanded="{{ $advancedFilterOpen ? 'true' : 'false' }}"
                            >
                                <span data-shop-filter-toggle-label>{{ $advancedFilterOpen ? 'بستن فیلترها' : 'فیلترهای بیشتر' }}</span>
                                @if ($activeFilterCount > 0)
                                    <span class="grid min-w-5 place-items-center rounded-full bg-brand px-1.5 py-0.5 text-[11px] leading-none text-white">{{ $activeFilterCount }}</span>
                                @endif
                                <span aria-hidden="true" class="text-sm leading-none">⌄</span>
                            </button>

                            @if ($activeFilterCount > 0)
                                <a href="{{ $listingRoute }}" class="col-span-2 inline-flex min-h-10 items-center justify-center rounded-full border border-border px-4 text-xs font-bold text-ink transition hover:border-brand hover:text-brand sm:col-span-1">
                                    حذف فیلترها
                                </a>
                            @endif
                        </div>
                    </div>

                    <div class="grid gap-2 md:flex md:items-start">
                        <details class="storefront-category-menu relative min-w-0 md:shrink-0">
                            <summary class="inline-flex min-h-10 w-full cursor-pointer items-center justify-center gap-2 rounded-full border border-brand bg-brand-soft px-4 text-sm font-bold text-brand transition hover:bg-white focus:outline-none focus:ring-2 focus:ring-brand focus:ring-offset-2 focus:ring-offset-surface md:w-auto">
                                <span>دسته‌بندی‌ها</span>
                                <span class="grid min-w-6 place-items-center rounded-full bg-brand px-2 py-0.5 text-xs leading-none text-white">{{ number_format($rootCategories->count()) }}</span>
                                <span aria-hidden="true" class="text-sm leading-none">⌄</span>
                            </summary>

                            <div class="storefront-category-menu-panel mt-2 gap-3 rounded-2xl border border-border bg-surface p-3 shadow-xl shadow-neutral-950/10 md:absolute md:right-0 md:top-full md:z-30 md:mt-3 md:w-[min(64rem,calc(100vw-3rem))] md:p-4">
                                <div class="grid gap-3 lg:grid-cols-[13rem_minmax(0,1fr)]">
                                    <nav class="grid content-start gap-1 rounded-xl border border-border bg-surface-alt p-2" aria-label="Top product categories">
                                        <a href="{{ route('storefront.shop') }}" class="flex min-h-10 items-center justify-between rounded-xl px-3 text-sm font-bold transition @if(! $category) bg-brand text-white shadow-sm shadow-red-900/10 @else text-ink hover:bg-surface hover:text-brand @endif">
                                            <span>همه محصولات</span>
                                            <span class="text-xs opacity-75">{{ number_format($products->total()) }}</span>
                                        </a>
                                        @foreach ($rootCategories as $root)
                                            @php
                                                $isActiveRoot = (int) $activeRootCategoryId === (int) $root->id;
                                            @endphp
                                            <a href="{{ route('storefront.categories.show', $root) }}" class="flex min-h-10 items-center justify-between rounded-xl px-3 text-sm font-bold transition @if($isActiveRoot) bg-brand-soft text-brand @else text-ink hover:bg-surface hover:text-brand @endif">
                                                <span>{{ $root->label }}</span>
                                                <span class="text-xs opacity-70">{{ $root->active_products_count }}</span>
                                            </a>
                                        @endforeach
                                    </nav>

                                    <div class="grid max-h-[28rem] gap-3 overflow-y-auto pl-1 sm:grid-cols-2 xl:grid-cols-3">
                                        @foreach ($rootCategories as $root)
                                            @php
                                                $children = $categoriesByParent->get($root->id, collect());
                                                $isActiveRoot = (int) $activeRootCategoryId === (int) $root->id;
                                            @endphp
                                            <section class="rounded-xl border p-3 @if($isActiveRoot) border-brand bg-brand-soft/60 @else border-border bg-surface @endif">
                                                <a href="{{ route('storefront.categories.show', $root) }}" class="flex items-center justify-between gap-3 text-sm font-bold text-ink transition hover:text-brand">
                                                    <span>{{ $root->label }}</span>
                                                    <span class="rounded-full bg-surface-alt px-2 py-0.5 text-xs text-muted">{{ $root->active_products_count }}</span>
                                                </a>

                                                @if ($children->isNotEmpty())
                                                    <div class="mt-3 grid gap-2">
                                                        @foreach ($children->take(6) as $child)
                                                            @php
                                                                $grandchildren = $categoriesByParent->get($child->id, collect());
                                                                $isActiveChild = in_array($child->id, $activeCategoryIds, true);
                                                            @endphp
                                                            <div class="grid gap-1 border-t border-border/70 pt-2 first:border-t-0 first:pt-0">
                                                                <a href="{{ route('storefront.categories.show', $child) }}" class="flex min-h-8 items-center justify-between gap-2 rounded-lg px-2 text-sm font-semibold transition @if($isActiveChild) bg-brand text-white @else text-ink hover:bg-surface-alt hover:text-brand @endif">
                                                                    <span>{{ $child->label }}</span>
                                                                    <span class="text-xs opacity-70">{{ $child->active_products_count }}</span>
                                                                </a>

                                                                @if ($grandchildren->isNotEmpty())
                                                                    <div class="mr-2 flex flex-wrap gap-1.5">
                                                                        @foreach ($grandchildren->take(4) as $grandchild)
                                                                            @php
                                                                                $isActiveGrandchild = in_array($grandchild->id, $activeCategoryIds, true);
                                                                            @endphp
                                                                            <a href="{{ route('storefront.categories.show', $grandchild) }}" class="rounded-full px-2.5 py-1 text-xs font-semibold transition @if($isActiveGrandchild) bg-brand-soft text-brand @else bg-surface-alt text-muted hover:text-brand @endif">
                                                                                {{ $grandchild->label }}
                                                                            </a>
                                                                        @endforeach
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @else
                                                    <p class="mt-3 text-xs font-medium text-muted">زیرگروه ثبت نشده</p>
                                                @endif
                                            </section>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </details>

                        <div class="min-w-0 max-w-full overflow-x-auto storefront-shop-scrollbar">
                            <nav class="inline-flex min-w-full gap-2 pb-1" aria-label="Product category shortcuts">
                                <a href="{{ route('storefront.shop') }}" class="storefront-mobile-category-link inline-flex min-h-10 items-center rounded-full border px-4 text-sm font-semibold transition @if(! $category) border-brand bg-brand text-white shadow-sm shadow-red-900/10 @else border-border bg-surface text-ink hover:border-brand hover:text-brand @endif">
                                    همه محصولات
                                </a>
                                @foreach ($rootCategories as $root)
                                    @php
                                        $isActiveRoot = (int) $activeRootCategoryId === (int) $root->id;
                                    @endphp
                                    <a href="{{ route('storefront.categories.show', $root) }}" class="storefront-mobile-category-link inline-flex min-h-10 items-center rounded-full border px-4 text-sm font-semibold transition @if($isActiveRoot) border-brand bg-brand text-white shadow-sm shadow-red-900/10 @else border-border bg-surface text-ink hover:border-brand hover:text-brand @endif">
                                        <span>{{ $root->label }}</span>
                                        <span class="mr-2 text-xs opacity-70">{{ $root->active_products_count }}</span>
                                    </a>
                                @endforeach
                            </nav>
                        </div>
                    </div>

                    @if ($activeFilterCount > 0 || $category)
                        <div class="flex flex-wrap gap-2 border-t border-border/70 pt-3 text-xs font-semibold">
                            @if ($category)
                                <span class="rounded-full bg-brand-soft px-3 py-1.5 text-brand">{{ $category->pathLabel() }}</span>
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
                            @if (! blank($filters['min_price'] ?? null))
                                <span class="rounded-full bg-surface-alt px-3 py-1.5 text-muted">قیمت از: {{ number_format((int) $filters['min_price']) }} تومان</span>
                            @endif
                            @if (! blank($filters['max_price'] ?? null))
                                <span class="rounded-full bg-surface-alt px-3 py-1.5 text-muted">قیمت تا: {{ number_format((int) $filters['max_price']) }} تومان</span>
                            @endif
                            @foreach ($selectedFeatures as $key => $value)
                                <span class="rounded-full bg-surface-alt px-3 py-1.5 text-muted">{{ $featureLabels->get($key, $key) }}: {{ $value }}</span>
                            @endforeach
                            @if (($filters['sort'] ?? 'recommended') !== 'recommended')
                                <span class="rounded-full bg-surface-alt px-3 py-1.5 text-muted">مرتب‌سازی: {{ $sortLabels[$filters['sort']] ?? $filters['sort'] }}</span>
                            @endif
                        </div>
                    @endif

                    <div id="shop-advanced-filters" class="grid gap-3 border-t border-border/70 pt-3" @if (! $advancedFilterOpen) hidden @endif>
                        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-6">
                            <label class="grid gap-2 text-sm font-semibold text-ink">
                                بازه‌های آماده
                                <select name="price" class="min-h-11 rounded-xl border border-border bg-surface px-3 text-sm font-medium text-ink outline-none focus:border-brand focus:ring-2 focus:ring-brand/20" data-shop-filter-control>
                                    <option value="">همه قیمت‌ها</option>
                                    @foreach ($priceLabels as $key => $label)
                                        <option value="{{ $key }}" @selected(($filters['price'] ?? '') === $key)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </label>

                            @foreach ($featureFilters['features'] as $feature)
                                <label class="grid gap-2 text-sm font-semibold text-ink">
                                    {{ $feature['label'] }}
                                    <select name="features[{{ $feature['key'] }}]" class="min-h-11 rounded-xl border border-border bg-surface px-3 text-sm font-medium text-ink outline-none focus:border-brand focus:ring-2 focus:ring-brand/20" data-shop-filter-control>
                                        <option value="">{{ $feature['placeholder'] }}</option>
                                        @foreach ($feature['values'] as $value)
                                            <option value="{{ $value }}" @selected(($filters['features'][$feature['key']] ?? '') === $value)>{{ $value }}</option>
                                        @endforeach
                                    </select>
                                </label>
                            @endforeach

                            <label class="grid gap-2 text-sm font-semibold text-ink">
                                قیمت از
                                <input name="min_price" type="number" min="0" value="{{ $filters['min_price'] ?? '' }}" placeholder="مثلاً ۵۰۰۰۰۰۰" inputmode="numeric" class="min-h-11 rounded-xl border border-border bg-surface px-3 text-sm font-medium text-ink outline-none placeholder:text-muted/60 focus:border-brand focus:ring-2 focus:ring-brand/20">
                            </label>

                            <label class="grid gap-2 text-sm font-semibold text-ink">
                                قیمت تا
                                <input name="max_price" type="number" min="0" value="{{ $filters['max_price'] ?? '' }}" placeholder="مثلاً ۱۵۰۰۰۰۰۰" inputmode="numeric" class="min-h-11 rounded-xl border border-border bg-surface px-3 text-sm font-medium text-ink outline-none placeholder:text-muted/60 focus:border-brand focus:ring-2 focus:ring-brand/20">
                            </label>
                        </div>

                        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-end">
                            <button type="submit" class="min-h-11 rounded-xl bg-brand px-4 text-sm font-semibold text-white shadow-sm shadow-red-900/15 transition hover:bg-brand-hover focus:outline-none focus:ring-2 focus:ring-brand focus:ring-offset-2 focus:ring-offset-surface">اعمال فیلتر</button>
                            @if ($activeFilterCount > 0)
                                <a href="{{ $listingRoute }}" class="inline-flex min-h-11 items-center justify-center rounded-xl border border-border px-4 text-sm font-semibold text-ink transition hover:border-brand hover:text-brand">حذف فیلترها</a>
                            @endif
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </section>

    <section class="bg-surface-page py-6 pb-12 sm:py-8">
        <div class="storefront-shop-container mx-auto grid min-w-0 max-w-7xl gap-5 px-4 sm:px-6 lg:px-8">
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
                <div class="grid grid-cols-[repeat(auto-fill,minmax(min(100%,38rem),1fr))] gap-4">
                    @foreach ($products as $product)
                        @include('storefront.partials.product-card', ['product' => $product])
                    @endforeach
                </div>

                <div class="mt-8">
                    {{ $products->links() }}
                </div>
            @endif
        </div>
    </section>
@endsection
