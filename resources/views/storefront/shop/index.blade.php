@extends('storefront.layouts.app')

@section('content')
    <section class="border-b border-neutral-200 bg-white">
        <div class="mx-auto grid max-w-7xl gap-5 px-4 py-8 sm:px-6 lg:px-8">
            @include('storefront.partials.breadcrumbs', ['items' => array_filter([
                ['name' => 'EtokBike', 'url' => route('storefront.home')],
                ['name' => 'فروشگاه', 'url' => route('storefront.shop')],
                $category ? ['name' => $category->label, 'url' => route('storefront.categories.show', $category)] : null,
            ])])
            <div class="flex flex-col justify-between gap-4 lg:flex-row lg:items-end">
                <div>
                    <h1 class="text-3xl font-bold tracking-normal text-neutral-950 sm:text-4xl">
                        {{ $category ? $category->label : 'فروشگاه دوچرخه EtokBike' }}
                    </h1>
                    <p class="mt-3 max-w-2xl leading-7 text-neutral-600">
                        محصولات، قیمت و موجودی برای خرید آنلاین و تحویل سریع.
                    </p>
                </div>
                <form action="{{ $category ? route('storefront.categories.show', $category) : route('storefront.shop') }}" method="GET" class="flex w-full gap-2 lg:max-w-md">
                    <label for="shop-search" class="sr-only">جستجوی محصول</label>
                    <input
                        id="shop-search"
                        name="q"
                        value="{{ $filters['q'] ?? '' }}"
                        type="search"
                        placeholder="جستجوی محصول"
                        class="min-h-11 min-w-0 flex-1 rounded-md border border-neutral-300 bg-white px-3 text-sm outline-none focus:border-red-700 focus:ring-2 focus:ring-red-700"
                    >
                    <button type="submit" class="min-h-11 rounded-md bg-neutral-950 px-4 text-sm font-semibold text-white hover:bg-red-700">جستجو</button>
                </form>
            </div>
        </div>
    </section>

    <section class="bg-[#f6f3ef] py-8">
        <div class="mx-auto grid max-w-7xl gap-6 px-4 sm:px-6 lg:grid-cols-[260px_1fr] lg:px-8">
            <aside class="lg:sticky lg:top-24 lg:self-start">
                <div class="rounded-lg border border-neutral-200 bg-white p-4">
                    <h2 class="text-base font-semibold text-neutral-950">دسته‌بندی</h2>
                    <div class="mt-4 grid gap-2">
                        <a href="{{ route('storefront.shop') }}" class="rounded-md px-3 py-2 text-sm font-medium hover:bg-neutral-100 @if(! $category) bg-neutral-950 text-white hover:bg-neutral-950 @endif">
                            همه محصولات
                        </a>
                        @foreach ($categories as $item)
                            <a href="{{ route('storefront.categories.show', $item) }}" class="rounded-md px-3 py-2 text-sm font-medium hover:bg-neutral-100 @if($category?->id === $item->id) bg-neutral-950 text-white hover:bg-neutral-950 @endif">
                                {{ $item->label }} <span class="text-xs opacity-70">({{ $item->active_products_count }})</span>
                            </a>
                        @endforeach
                    </div>

                    <form action="{{ $category ? route('storefront.categories.show', $category) : route('storefront.shop') }}" method="GET" class="mt-6 grid gap-4 border-t border-neutral-200 pt-4">
                        @if (! blank($filters['q'] ?? null))
                            <input type="hidden" name="q" value="{{ $filters['q'] }}">
                        @endif
                        <label class="grid gap-2 text-sm font-medium text-neutral-800">
                            موجودی
                            <select name="availability" class="min-h-10 rounded-md border border-neutral-300 bg-white px-3 text-sm">
                                <option value="">همه</option>
                                @foreach (\App\Models\Product::AVAILABILITY_OPTIONS as $key => $label)
                                    <option value="{{ $key }}" @selected(($filters['availability'] ?? '') === $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label class="grid gap-2 text-sm font-medium text-neutral-800">
                            قیمت
                            <select name="price" class="min-h-10 rounded-md border border-neutral-300 bg-white px-3 text-sm">
                                <option value="">همه قیمت‌ها</option>
                                <option value="under_2m" @selected(($filters['price'] ?? '') === 'under_2m')>زیر ۲ میلیون</option>
                                <option value="2m_20m" @selected(($filters['price'] ?? '') === '2m_20m')>۲ تا ۲۰ میلیون</option>
                                <option value="over_20m" @selected(($filters['price'] ?? '') === 'over_20m')>بالای ۲۰ میلیون</option>
                            </select>
                        </label>
                        <label class="grid gap-2 text-sm font-medium text-neutral-800">
                            مرتب‌سازی
                            <select name="sort" class="min-h-10 rounded-md border border-neutral-300 bg-white px-3 text-sm">
                                <option value="recommended" @selected(($filters['sort'] ?? 'recommended') === 'recommended')>پیشنهادی</option>
                                <option value="price_low" @selected(($filters['sort'] ?? '') === 'price_low')>ارزان‌ترین</option>
                                <option value="price_high" @selected(($filters['sort'] ?? '') === 'price_high')>گران‌ترین</option>
                                <option value="newest" @selected(($filters['sort'] ?? '') === 'newest')>جدیدترین</option>
                            </select>
                        </label>
                        <button type="submit" class="min-h-10 rounded-md bg-neutral-950 px-4 text-sm font-semibold text-white hover:bg-red-700">اعمال فیلتر</button>
                        <a href="{{ $category ? route('storefront.categories.show', $category) : route('storefront.shop') }}" class="text-center text-sm font-semibold text-red-700 hover:text-red-900">حذف فیلترها</a>
                    </form>
                </div>
            </aside>

            <div>
                <div class="mb-4 flex items-center justify-between gap-3 text-sm text-neutral-600">
                    <p>{{ $products->total() }} محصول</p>
                    @if (! blank($filters['q'] ?? null))
                        <p>نتیجه برای: <span class="font-semibold text-neutral-950">{{ $filters['q'] }}</span></p>
                    @endif
                </div>

                @if ($products->isEmpty())
                    <div class="rounded-lg border border-neutral-200 bg-white p-8 text-center">
                        <h2 class="text-xl font-semibold text-neutral-950">محصولی پیدا نشد</h2>
                        <p class="mt-2 text-neutral-600">فیلترها را تغییر دهید یا همه محصولات را ببینید.</p>
                        <a href="{{ $category ? route('storefront.categories.show', $category) : route('storefront.shop') }}" class="mt-5 inline-flex min-h-10 items-center rounded-md bg-neutral-950 px-4 text-sm font-semibold text-white hover:bg-red-700">بازگشت</a>
                    </div>
                @else
                    <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
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
