@extends('storefront.layouts.app')

@section('og_type', 'product')

@php
    $isUnavailable = $product->availability === 'out_of_stock';
    $availabilityLabel = $product->stock_label ?: match ($product->availability) {
        'in_stock' => 'موجود',
        'low_stock' => 'موجودی محدود',
        'orderable' => 'قابل سفارش',
        'out_of_stock' => 'ناموجود',
        default => \App\Models\Product::AVAILABILITY_OPTIONS[$product->availability] ?? 'وضعیت نامشخص',
    };
@endphp

@section('content')
    <section class="storefront-page-hero">
        <div class="mx-auto grid max-w-7xl gap-6 px-4 py-6 sm:px-6 sm:py-10 lg:grid-cols-2 lg:gap-10 lg:px-8">
            <div class="grid gap-5">
                @include('storefront.partials.breadcrumbs', ['items' => [
                    ['name' => 'EtokBike', 'url' => route('storefront.home')],
                    ['name' => 'فروشگاه', 'url' => route('storefront.shop')],
                    ['name' => $product->category->label, 'url' => route('storefront.categories.show', $product->category)],
                    ['name' => $product->title, 'url' => route('storefront.products.show', $product)],
                ]])
                <div class="storefront-surface-card overflow-hidden p-2">
                    @include('storefront.partials.product-visual', ['product' => $product, 'class' => 'aspect-[4/3]', 'radius' => 'rounded-xl', 'loading' => 'eager'])
                </div>
            </div>

            <article class="storefront-surface-card self-start p-5 sm:p-6 lg:sticky lg:top-24">
                <p class="storefront-eyebrow">{{ $product->category->label }}</p>
                <h1 class="mt-4 text-3xl font-extrabold leading-tight tracking-normal text-ink sm:text-4xl">{{ $product->title }}</h1>
                @if ($product->subtitle)
                    <p class="mt-3 text-base leading-8 text-muted sm:text-lg">{{ $product->subtitle }}</p>
                @endif

                <dl class="mt-6 grid gap-3 rounded-2xl bg-surface-alt p-4 sm:grid-cols-2">
                    <div>
                        <dt class="text-sm font-medium text-muted">قیمت امروز</dt>
                        <dd class="mt-1 text-2xl font-extrabold text-ink">{{ $product->price_label ?: \App\Support\Storefront\PriceFormatter::format($product->price_value) }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-muted">وضعیت موجودی</dt>
                        <dd class="mt-1 inline-flex rounded-full px-2.5 py-1 text-sm font-bold @if($product->availability === 'in_stock') bg-teal-50 text-teal-700 @elseif($product->availability === 'low_stock') bg-amber-50 text-amber-700 @elseif($product->availability === 'orderable') bg-brand-soft text-brand @else bg-neutral-200 text-muted @endif">{{ $availabilityLabel }}</dd>
                    </div>
                </dl>

                <form method="POST" action="{{ route('storefront.cart.items.store', $product) }}" class="mt-5 grid gap-3 sm:grid-cols-[120px_1fr]">
                    @csrf
                    <label class="grid gap-2 text-sm font-semibold text-ink">
                        تعداد
                        <input type="number" name="quantity" min="1" max="20" value="1" class="storefront-control px-3">
                    </label>
                    <button
                        type="submit"
                        @disabled($isUnavailable)
                        class="self-end min-h-12 rounded-xl bg-brand px-6 text-sm font-bold text-white shadow-sm shadow-red-900/15 transition hover:bg-brand-hover focus:outline-none focus:ring-2 focus:ring-brand focus:ring-offset-2 disabled:cursor-not-allowed disabled:bg-neutral-200 disabled:text-muted"
                    >
                        {{ $isUnavailable ? 'ناموجود' : 'افزودن به سبد خرید' }}
                    </button>
                </form>

                <div class="mt-5 flex flex-wrap gap-x-5 gap-y-2 border-t border-border pt-4 text-sm font-semibold text-muted">
                    <span>موجودی و قیمت به‌روز</span>
                    <span>ثبت سفارش مستقیم</span>
                    <a href="{{ route('storefront.messages') }}" class="text-brand transition hover:text-brand-hover">نیاز به راهنمایی دارید؟</a>
                </div>
            </article>
        </div>
    </section>

    @if ($product->description)
        <section class="bg-surface py-10 sm:py-12">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="max-w-3xl">
                    <p class="storefront-eyebrow">معرفی محصول</p>
                    <h2 class="mt-3 text-2xl font-bold text-ink">جزئیات و مشخصات</h2>
                    <div class="product-rich-content mt-5 text-muted">
                        {{ $product->richDescription() }}
                    </div>
                </div>
            </div>
        </section>
    @endif

    @if ($relatedProducts->isNotEmpty())
        <section class="border-t border-border bg-surface-page py-10 sm:py-12">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <p class="storefront-eyebrow">پیشنهادهای مشابه</p>
                <h2 class="mt-3 text-2xl font-bold tracking-normal text-ink">محصولات مرتبط</h2>
                <div class="mt-6 grid grid-cols-[repeat(auto-fill,minmax(min(100%,38rem),1fr))] gap-4">
                    @foreach ($relatedProducts as $relatedProduct)
                        @include('storefront.partials.product-card', ['product' => $relatedProduct])
                    @endforeach
                </div>
            </div>
        </section>
    @endif
@endsection
