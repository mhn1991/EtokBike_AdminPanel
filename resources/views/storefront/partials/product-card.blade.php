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

<article class="group flex h-full max-h-[34rem] flex-col overflow-hidden rounded-2xl border border-border bg-surface shadow-sm shadow-neutral-950/5 transition duration-200 hover:-translate-y-0.5 hover:border-red-200 hover:shadow-lg hover:shadow-neutral-950/10" itemscope itemtype="https://schema.org/Product">
    <a href="{{ route('storefront.products.show', $product) }}" class="block" itemprop="url">
        @include('storefront.partials.product-visual', ['product' => $product, 'class' => 'h-56 sm:h-72', 'radius' => 'rounded-t-2xl', 'loading' => 'lazy'])
    </a>
    <div class="flex flex-1 flex-col gap-3 p-4 sm:p-5">
        <div class="grid gap-2">
            <div class="flex items-start justify-between gap-3">
                <p class="rounded-full bg-brand-soft px-3 py-1 text-xs font-semibold text-brand">{{ $product->category?->label ?: 'محصول' }}</p>
                <p class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-semibold
                    @if($product->availability === 'in_stock') border-teal-100 bg-teal-50 text-teal-700
                    @elseif($product->availability === 'low_stock') border-amber-100 bg-amber-50 text-amber-700
                    @elseif($product->availability === 'orderable') border-red-100 bg-brand-soft text-brand
                    @else border-neutral-200 bg-neutral-100 text-muted @endif">
                    {{ $availabilityLabel }}
                </p>
            </div>
            <h2 class="text-lg font-semibold leading-7 text-ink" itemprop="name">
                <a href="{{ route('storefront.products.show', $product) }}" class="line-clamp-2 transition hover:text-brand">{{ $product->title }}</a>
            </h2>
            @if ($product->subtitle)
                <p class="line-clamp-2 text-sm leading-6 text-muted" itemprop="description">{{ $product->subtitle }}</p>
            @endif
        </div>

        <div class="mt-auto grid gap-3 border-t border-border pt-3">
            <p class="text-xl font-bold text-ink" itemprop="offers" itemscope itemtype="https://schema.org/Offer">
                <meta itemprop="priceCurrency" content="IRR">
                <meta itemprop="price" content="{{ $product->price_value }}">
                <meta itemprop="availability" content="{{ $isUnavailable ? 'https://schema.org/OutOfStock' : 'https://schema.org/InStock' }}">
                {{ $product->price_label ?: \App\Support\Storefront\PriceFormatter::format($product->price_value) }}
            </p>

            <form method="POST" action="{{ route('storefront.cart.items.store', $product) }}">
                @csrf
                <input type="hidden" name="quantity" value="1">
                <button
                    type="submit"
                    @disabled($isUnavailable)
                    class="inline-flex min-h-11 w-full items-center justify-center rounded-xl bg-brand px-4 py-2 text-sm font-semibold text-white shadow-sm shadow-red-900/15 transition hover:bg-brand-hover focus:outline-none focus:ring-2 focus:ring-brand focus:ring-offset-2 focus:ring-offset-surface disabled:cursor-not-allowed disabled:bg-neutral-200 disabled:text-muted disabled:shadow-none"
                >
                    {{ $isUnavailable ? 'ناموجود' : 'افزودن به سبد' }}
                </button>
            </form>
        </div>
    </div>
</article>
