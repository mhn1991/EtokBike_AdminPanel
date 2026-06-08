@extends('storefront.layouts.app')

@section('og_type', 'product')

@section('content')
    <section class="bg-white">
        <div class="mx-auto grid max-w-7xl gap-8 px-4 py-8 sm:px-6 lg:grid-cols-2 lg:px-8">
            <div class="grid gap-5">
                @include('storefront.partials.breadcrumbs', ['items' => [
                    ['name' => 'EtokBike', 'url' => route('storefront.home')],
                    ['name' => 'فروشگاه', 'url' => route('storefront.shop')],
                    ['name' => $product->category->label, 'url' => route('storefront.categories.show', $product->category)],
                    ['name' => $product->title, 'url' => route('storefront.products.show', $product)],
                ]])
                @include('storefront.partials.product-visual', ['product' => $product, 'class' => 'aspect-[4/3]', 'loading' => 'eager'])
            </div>

            <article class="self-center">
                <p class="text-sm font-semibold text-red-700">{{ $product->category->label }}</p>
                <h1 class="mt-3 text-3xl font-bold leading-tight tracking-normal text-neutral-950 sm:text-4xl">{{ $product->title }}</h1>
                <p class="mt-4 text-lg leading-8 text-neutral-700">{{ $product->subtitle }}</p>

                @if ($product->description)
                    <p class="mt-4 leading-8 text-neutral-600">{{ $product->description }}</p>
                @endif

                <dl class="mt-6 grid gap-3 border-y border-neutral-200 py-5 sm:grid-cols-2">
                    <div>
                        <dt class="text-sm text-neutral-500">قیمت</dt>
                        <dd class="mt-1 text-xl font-semibold text-neutral-950">{{ $product->price_label ?: \App\Support\Storefront\PriceFormatter::format($product->price_value) }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-neutral-500">موجودی</dt>
                        <dd class="mt-1 font-semibold text-neutral-950">{{ $product->stock_label ?: \App\Models\Product::AVAILABILITY_OPTIONS[$product->availability] }}</dd>
                    </div>
                </dl>

                <form method="POST" action="{{ route('storefront.cart.items.store', $product) }}" class="mt-6 grid gap-3 sm:grid-cols-[120px_1fr]">
                    @csrf
                    <label class="grid gap-2 text-sm font-medium text-neutral-800">
                        تعداد
                        <input
                            type="number"
                            name="quantity"
                            min="1"
                            max="20"
                            value="1"
                            class="min-h-12 rounded-md border border-neutral-300 px-3 text-neutral-950"
                        >
                    </label>
                    <button
                        type="submit"
                        @disabled($product->availability === 'out_of_stock')
                        class="self-end min-h-12 rounded-md bg-neutral-950 px-6 text-sm font-semibold text-white hover:bg-red-700 disabled:cursor-not-allowed disabled:bg-neutral-300 disabled:text-neutral-600"
                    >
                        {{ $product->availability === 'out_of_stock' ? 'ناموجود' : 'افزودن به سبد خرید' }}
                    </button>
                </form>
            </article>
        </div>
    </section>

    @if ($relatedProducts->isNotEmpty())
        <section class="border-t border-neutral-200 bg-[#f6f3ef] py-10">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <h2 class="text-2xl font-semibold tracking-normal text-neutral-950">محصولات مرتبط</h2>
                <div class="mt-6 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($relatedProducts as $relatedProduct)
                        @include('storefront.partials.product-card', ['product' => $relatedProduct])
                    @endforeach
                </div>
            </div>
        </section>
    @endif
@endsection
