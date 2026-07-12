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
    <section class="bg-surface">
        <div class="mx-auto grid max-w-7xl gap-8 px-4 py-8 sm:px-6 lg:grid-cols-2 lg:px-8">
            <div class="grid gap-5">
                @include('storefront.partials.breadcrumbs', ['items' => [
                    ['name' => 'EtokBike', 'url' => route('storefront.home')],
                    ['name' => 'فروشگاه', 'url' => route('storefront.shop')],
                    ['name' => $product->category->label, 'url' => route('storefront.categories.show', $product->category)],
                    ['name' => $product->title, 'url' => route('storefront.products.show', $product)],
                ]])
                @include('storefront.partials.product-visual', ['product' => $product, 'class' => 'aspect-[4/3]', 'radius' => 'rounded-2xl', 'loading' => 'eager'])
            </div>

            <article class="self-center">
                <p class="inline-flex rounded-full bg-brand-soft px-3 py-1 text-sm font-semibold text-brand">{{ $product->category->label }}</p>
                <h1 class="mt-4 text-3xl font-bold leading-tight tracking-normal text-ink sm:text-4xl">{{ $product->title }}</h1>
                <p class="mt-4 text-lg leading-8 text-muted">{{ $product->subtitle }}</p>

                @if ($product->description)
                    <div class="product-rich-content mt-5 text-muted">
                        {{ $product->richDescription() }}
                    </div>
                @endif

                <dl class="mt-6 grid gap-3 border-y border-border py-5 sm:grid-cols-2">
                    <div>
                        <dt class="text-sm text-muted">قیمت</dt>
                        <dd class="mt-1 text-2xl font-bold text-ink">{{ $product->price_label ?: \App\Support\Storefront\PriceFormatter::format($product->price_value) }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-muted">موجودی</dt>
                        <dd class="mt-1 font-semibold text-ink">{{ $availabilityLabel }}</dd>
                    </div>
                </dl>

                <form method="POST" action="{{ route('storefront.cart.items.store', $product) }}" class="mt-6 grid gap-3 sm:grid-cols-[120px_1fr]">
                    @csrf
                    <label class="grid gap-2 text-sm font-semibold text-ink">
                        تعداد
                        <input
                            type="number"
                            name="quantity"
                            min="1"
                            max="20"
                            value="1"
                            class="storefront-control px-3"
                        >
                    </label>
                    <button
                        type="submit"
                        @disabled($isUnavailable)
                        class="self-end min-h-12 rounded-xl bg-brand px-6 text-sm font-semibold text-white transition hover:bg-brand-hover disabled:cursor-not-allowed disabled:bg-neutral-200 disabled:text-muted"
                    >
                        {{ $isUnavailable ? 'ناموجود' : 'افزودن به سبد خرید' }}
                    </button>
                </form>
            </article>
        </div>
    </section>

    @if ($relatedProducts->isNotEmpty())
        <section class="border-t border-border bg-surface-page py-10">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <h2 class="text-2xl font-semibold tracking-normal text-ink">محصولات مرتبط</h2>
                <div class="mt-6 grid gap-5 sm:grid-cols-2 lg:grid-cols-4 2xl:grid-cols-5">
                    @foreach ($relatedProducts as $relatedProduct)
                        @include('storefront.partials.product-card', ['product' => $relatedProduct])
                    @endforeach
                </div>
            </div>
        </section>
    @endif
@endsection
