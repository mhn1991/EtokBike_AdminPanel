@php
    $isUnavailable = $product->availability === 'out_of_stock';
@endphp

<article class="group overflow-hidden rounded-lg border border-neutral-200 bg-white" itemscope itemtype="https://schema.org/Product">
    <a href="{{ route('storefront.products.show', $product) }}" class="block" itemprop="url">
        @include('storefront.partials.product-visual', ['product' => $product, 'class' => 'aspect-[4/3]', 'loading' => 'lazy'])
    </a>
    <div class="grid gap-4 p-4">
        <div class="grid gap-2">
            <p class="text-xs font-semibold text-red-700">{{ $product->category?->label }}</p>
            <h2 class="text-lg font-semibold leading-7 text-neutral-950" itemprop="name">
                <a href="{{ route('storefront.products.show', $product) }}" class="hover:text-red-700">{{ $product->title }}</a>
            </h2>
            <p class="text-sm leading-6 text-neutral-600" itemprop="description">{{ $product->subtitle }}</p>
        </div>

        <div class="flex items-center justify-between gap-3">
            <p class="font-semibold text-neutral-950" itemprop="offers" itemscope itemtype="https://schema.org/Offer">
                <meta itemprop="priceCurrency" content="IRR">
                <meta itemprop="price" content="{{ $product->price_value }}">
                {{ $product->price_label ?: \App\Support\Storefront\PriceFormatter::format($product->price_value) }}
            </p>
            <p class="text-xs font-medium text-neutral-500">{{ $product->stock_label ?: \App\Models\Product::AVAILABILITY_OPTIONS[$product->availability] }}</p>
        </div>

        <form method="POST" action="{{ route('storefront.cart.items.store', $product) }}">
            @csrf
            <input type="hidden" name="quantity" value="1">
            <button
                type="submit"
                @disabled($isUnavailable)
                class="inline-flex min-h-10 w-full items-center justify-center rounded-md bg-neutral-950 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700 disabled:cursor-not-allowed disabled:bg-neutral-300 disabled:text-neutral-600"
            >
                {{ $isUnavailable ? 'ناموجود' : 'افزودن به سبد' }}
            </button>
        </form>
    </div>
</article>
