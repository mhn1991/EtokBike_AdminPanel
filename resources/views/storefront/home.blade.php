@extends('storefront.layouts.app')

@section('content')
    <section class="relative isolate min-h-[40rem] overflow-hidden bg-neutral-950 text-white">
        <img
            src="{{ asset('images/storefront/hero-shop.png') }}"
            alt="EtokBike bicycle shop"
            class="absolute inset-0 h-full w-full object-cover"
            loading="eager"
            fetchpriority="high"
        >
        <div class="absolute inset-0 bg-gradient-to-l from-neutral-950 via-neutral-950/82 to-neutral-950/35"></div>
        <div class="relative mx-auto grid min-h-[40rem] max-w-7xl content-center gap-10 px-4 py-14 sm:px-6 lg:grid-cols-[1.1fr_0.9fr] lg:px-8">
            <div class="max-w-2xl">
                <p class="inline-flex rounded-full border border-white/20 bg-white/10 px-3 py-1.5 text-sm font-bold text-red-100 backdrop-blur">فروشگاه و سرویس دوچرخه</p>
                <h1 class="mt-5 text-4xl font-extrabold leading-[1.25] tracking-normal sm:text-5xl lg:text-6xl">همراه مسیرهای روزانه و ماجراجویی‌های تازه</h1>
                <p class="mt-5 max-w-xl text-base leading-8 text-neutral-100 sm:text-lg">
                    دوچرخه، قطعات و لوازم جانبی را با موجودی به‌روز انتخاب کنید؛ برای سرویس هم مستقیم و بدون تماس‌های رفت‌وبرگشتی وقت بگیرید.
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
                    <button type="submit" class="min-h-12 rounded-xl bg-brand px-6 text-sm font-bold text-white shadow-lg shadow-red-950/30 transition hover:bg-brand-hover focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-neutral-950">
                        جستجو
                    </button>
                </form>
                <div class="mt-5 flex flex-col gap-3 text-sm font-bold sm:flex-row">
                    <a href="{{ route('storefront.shop') }}" class="inline-flex min-h-11 items-center justify-center rounded-xl bg-white px-5 text-neutral-950 transition hover:bg-brand hover:text-white">خرید دوچرخه و لوازم</a>
                    <a href="{{ route('storefront.services') }}" class="inline-flex min-h-11 items-center justify-center rounded-xl border border-white/40 px-5 text-white transition hover:border-white hover:bg-white hover:text-neutral-950">رزرو سرویس</a>
                </div>
            </div>

            <aside class="grid gap-3 rounded-3xl border border-white/15 bg-white/10 p-4 backdrop-blur-sm sm:grid-cols-3 lg:grid-cols-1">
                <div class="rounded-2xl border border-white/10 bg-neutral-950/25 p-4">
                    <p class="text-2xl font-extrabold text-white">{{ number_format($catalogueProductCount) }}+</p>
                    <p class="mt-1 text-sm font-medium text-neutral-200">محصول قابل بررسی</p>
                </div>
                <div class="rounded-2xl border border-white/10 bg-neutral-950/25 p-4">
                    <p class="text-2xl font-extrabold text-white">{{ number_format($categories->count()) }}</p>
                    <p class="mt-1 text-sm font-medium text-neutral-200">دسته برای انتخاب سریع</p>
                </div>
                <div class="rounded-2xl border border-white/10 bg-neutral-950/25 p-4">
                    <p class="text-2xl font-extrabold text-white">یکجا</p>
                    <p class="mt-1 text-sm font-medium text-neutral-200">خرید، سرویس و پیگیری</p>
                </div>
            </aside>
        </div>
    </section>

    <section class="storefront-page-hero py-12 sm:py-16">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
                <div>
                    <p class="storefront-eyebrow">شروع سریع</p>
                    <h2 class="mt-3 text-2xl font-bold tracking-normal text-ink sm:text-3xl">برای چه چیزی رکاب می‌زنید؟</h2>
                    <p class="mt-2 text-sm leading-6 text-muted">دسته‌بندی‌ها را ببینید و سریع‌تر به انتخاب مناسب برسید.</p>
                </div>
                <a href="{{ route('storefront.shop') }}" class="inline-flex min-h-11 items-center justify-center rounded-xl border border-border bg-surface px-4 text-sm font-bold text-ink transition hover:border-brand hover:text-brand">مشاهده همه محصولات</a>
            </div>

            <div class="mt-7 grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                @foreach ($categories as $category)
                    <a href="{{ route('storefront.categories.show', $category) }}" class="group storefront-surface-card relative overflow-hidden p-5 transition hover:-translate-y-0.5 hover:border-brand hover:shadow-lg">
                        <span class="text-sm font-bold text-brand">{{ number_format($category->active_products_count) }} محصول</span>
                        <h3 class="mt-3 text-xl font-bold text-ink">{{ $category->label }}</h3>
                        <p class="mt-2 text-sm leading-6 text-muted">مشاهده مدل‌ها و لوازم این دسته</p>
                        <span class="mt-5 inline-flex items-center gap-2 text-sm font-bold text-ink transition group-hover:text-brand">مشاهده دسته <span aria-hidden="true">←</span></span>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    <section class="bg-surface py-12 sm:py-16">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
                <div>
                    <p class="storefront-eyebrow">پیشنهاد فروشگاه</p>
                    <h2 class="mt-3 text-2xl font-bold tracking-normal text-ink sm:text-3xl">انتخاب‌های آماده برای مسیر بعدی</h2>
                    <p class="mt-2 text-sm leading-6 text-muted">موجودی، قیمت و امکان افزودن به سبد را یک‌جا ببینید.</p>
                </div>
                <a href="{{ route('storefront.shop') }}" class="text-sm font-bold text-brand transition hover:text-brand-hover">دیدن فروشگاه ←</a>
            </div>

            <div class="mt-7 grid grid-cols-[repeat(auto-fill,minmax(min(100%,38rem),1fr))] gap-4">
                @foreach ($featuredProducts as $product)
                    @include('storefront.partials.product-card', ['product' => $product])
                @endforeach
            </div>
        </div>
    </section>

    @if ($storeProfile)
        <section class="border-t border-border bg-surface-page py-12">
            <div class="mx-auto grid max-w-7xl gap-4 px-4 sm:px-6 md:grid-cols-2 lg:px-8">
                <div class="storefront-surface-card p-5 sm:p-6">
                    <p class="storefront-eyebrow">اعتماد و پشتیبانی</p>
                    <h2 class="mt-4 text-xl font-bold text-ink">{{ $storeProfile->status_title }}</h2>
                    <p class="mt-2 leading-7 text-muted">{{ $storeProfile->status_description }}</p>
                </div>
                <div class="storefront-surface-card p-5 sm:p-6">
                    <p class="storefront-eyebrow">فروشگاه حضوری</p>
                    <h2 class="mt-4 text-xl font-bold text-ink">{{ $storeProfile->branch_title }}</h2>
                    <p class="mt-2 leading-7 text-muted">{{ $storeProfile->address }}</p>
                    <p class="mt-1 leading-7 text-muted">{{ $storeProfile->hours }}</p>
                </div>
            </div>
        </section>
    @endif
@endsection
