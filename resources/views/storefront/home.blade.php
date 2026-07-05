@extends('storefront.layouts.app')

@section('content')
    <section class="relative min-h-[64vh] overflow-hidden bg-neutral-950 text-white">
        <img
            src="{{ asset('images/storefront/hero-shop.png') }}"
            alt="EtokBike bicycle shop"
            class="absolute inset-0 h-full w-full object-cover"
            loading="eager"
            fetchpriority="high"
        >
        <div class="absolute inset-0 bg-gradient-to-l from-black/80 via-black/45 to-black/20"></div>
        <div class="relative mx-auto grid min-h-[64vh] max-w-7xl content-center gap-8 px-4 py-16 sm:px-6 lg:px-8">
            <div class="max-w-2xl">
                <p class="text-sm font-semibold text-red-200">فروشگاه و سرویس دوچرخه</p>
                <h1 class="mt-4 text-4xl font-bold leading-tight tracking-normal sm:text-5xl lg:text-6xl">EtokBike</h1>
                <p class="mt-5 max-w-xl text-lg leading-8 text-neutral-100">
                    دوچرخه، قطعات و لوازم جانبی را با موجودی به‌روز، ثبت سفارش مستقیم و مدیریت یکپارچه از همین فروشگاه بخرید.
                </p>
                <form action="{{ route('storefront.shop') }}" method="GET" class="mt-8 flex max-w-xl flex-col gap-3 sm:flex-row">
                    <label for="home-search" class="sr-only">جستجوی محصول</label>
                    <input
                        id="home-search"
                        name="q"
                        type="search"
                        placeholder="نام دوچرخه، قطعه یا لوازم جانبی"
                        class="min-h-12 flex-1 rounded-xl border border-white/20 bg-white px-4 text-neutral-950 outline-none focus:border-brand focus:ring-2 focus:ring-brand"
                    >
                    <button type="submit" class="min-h-12 rounded-xl bg-brand px-6 text-sm font-semibold text-white transition hover:bg-brand-hover">
                        جستجو
                    </button>
                </form>
                <div class="mt-5 flex flex-wrap gap-3 text-sm font-semibold">
                    <a href="{{ route('storefront.shop') }}" class="rounded-xl bg-white px-4 py-2 text-neutral-950 transition hover:bg-brand hover:text-white">دیدن فروشگاه</a>
                    <a href="{{ route('storefront.cart.show') }}" class="rounded-xl border border-white/40 px-4 py-2 text-white transition hover:border-white hover:bg-white hover:text-neutral-950">سبد خرید</a>
                </div>
            </div>
        </div>
    </section>

    <section class="bg-surface-page py-10">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
                <div>
                    <h2 class="text-2xl font-semibold tracking-normal text-ink">دسته‌بندی محصولات</h2>
                    <p class="mt-2 text-sm leading-6 text-muted">دسته‌های اصلی فروشگاه برای انتخاب سریع‌تر.</p>
                </div>
                <a href="{{ route('storefront.shop') }}" class="text-sm font-semibold text-brand transition hover:text-brand-hover">مشاهده همه محصولات</a>
            </div>

            <div class="mt-6 grid gap-4 sm:grid-cols-3 xl:grid-cols-4">
                @foreach ($categories as $category)
                    <a href="{{ route('storefront.categories.show', $category) }}" class="rounded-2xl border border-border bg-surface p-5 shadow-sm transition hover:border-brand">
                        <span class="text-sm font-semibold text-brand">{{ $category->active_products_count }} محصول</span>
                        <h3 class="mt-3 text-xl font-semibold text-ink">{{ $category->label }}</h3>
                        <p class="mt-2 text-sm text-muted">مشاهده محصولات دسته {{ $category->label }}</p>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    <section class="bg-surface py-12">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
                <div>
                    <h2 class="text-2xl font-semibold tracking-normal text-ink">محصولات پیشنهادی</h2>
                    <p class="mt-2 text-sm leading-6 text-muted">انتخاب‌های مناسب برای شروع خرید.</p>
                </div>
            </div>

            <div class="mt-6 grid gap-5 sm:grid-cols-2 lg:grid-cols-3 2xl:grid-cols-4">
                @foreach ($featuredProducts as $product)
                    @include('storefront.partials.product-card', ['product' => $product])
                @endforeach
            </div>
        </div>
    </section>

    @if ($storeProfile)
        <section class="border-t border-border bg-surface-page py-10">
            <div class="mx-auto grid max-w-7xl gap-6 px-4 sm:px-6 md:grid-cols-2 lg:px-8">
                <div>
                    <h2 class="text-xl font-semibold text-ink">{{ $storeProfile->status_title }}</h2>
                    <p class="mt-2 leading-7 text-muted">{{ $storeProfile->status_description }}</p>
                </div>
                <div>
                    <h2 class="text-xl font-semibold text-ink">{{ $storeProfile->branch_title }}</h2>
                    <p class="mt-2 leading-7 text-muted">{{ $storeProfile->address }}</p>
                    <p class="mt-1 leading-7 text-muted">{{ $storeProfile->hours }}</p>
                </div>
            </div>
        </section>
    @endif
@endsection
