@extends('storefront.layouts.app')

@section('content')
    <section class="bg-white">
        <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <h1 class="text-3xl font-bold tracking-normal text-neutral-950">سبد خرید</h1>
        </div>
    </section>

    <section class="bg-[#f6f3ef] py-8">
        @if ($lines->isEmpty())
            <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
                <div class="rounded-lg border border-neutral-200 bg-white p-8 text-center">
                    <p class="text-sm font-semibold text-red-700">سبد خرید</p>
                    <h2 class="mt-3 text-2xl font-bold text-neutral-950">سبد خرید خالی است</h2>
                    <p class="mx-auto mt-3 max-w-md leading-7 text-neutral-600">برای ثبت سفارش، ابتدا یک دوچرخه، قطعه یا لوازم جانبی انتخاب کنید.</p>
                    <div class="mt-6 flex flex-col justify-center gap-3 sm:flex-row">
                        <a href="{{ route('storefront.shop') }}" class="inline-flex min-h-11 items-center justify-center rounded-md bg-neutral-950 px-5 text-sm font-semibold text-white hover:bg-red-700">رفتن به فروشگاه</a>
                        <a href="{{ route('storefront.home') }}" class="inline-flex min-h-11 items-center justify-center rounded-md border border-neutral-300 bg-white px-5 text-sm font-semibold text-neutral-950 hover:border-red-700 hover:text-red-700">بازگشت به خانه</a>
                    </div>
                </div>
            </div>
        @else
            <div class="mx-auto grid max-w-7xl gap-6 px-4 sm:px-6 lg:grid-cols-[1fr_360px] lg:px-8">
                <div class="grid gap-4">
                    @foreach ($lines as $line)
                    @php($product = $line['product'])
                    <article class="grid gap-4 rounded-lg border border-neutral-200 bg-white p-4 sm:grid-cols-[140px_1fr]">
                        @include('storefront.partials.product-visual', ['product' => $product, 'class' => 'aspect-[4/3] sm:aspect-square', 'loading' => 'lazy'])
                        <div class="grid gap-4">
                            <div>
                                <h2 class="text-lg font-semibold text-neutral-950">
                                    <a href="{{ route('storefront.products.show', $product) }}" class="hover:text-red-700">{{ $product->title }}</a>
                                </h2>
                                <p class="mt-1 text-sm text-neutral-600">{{ $product->subtitle }}</p>
                            </div>
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                                <form method="POST" action="{{ route('storefront.cart.items.update', $product) }}" class="flex items-end gap-2">
                                    @csrf
                                    @method('PATCH')
                                    <label class="grid gap-1 text-sm font-medium text-neutral-800">
                                        تعداد
                                        <input name="quantity" type="number" min="1" max="20" value="{{ $line['quantity'] }}" class="h-10 w-24 rounded-md border border-neutral-300 px-3">
                                    </label>
                                    <button type="submit" class="h-10 rounded-md border border-neutral-300 bg-white px-3 text-sm font-semibold hover:border-red-700 hover:text-red-700">به‌روزرسانی</button>
                                </form>
                                <div class="flex items-center justify-between gap-4">
                                    <p class="font-semibold text-neutral-950">{{ \App\Support\Storefront\PriceFormatter::format($line['line_total']) }}</p>
                                    <form method="POST" action="{{ route('storefront.cart.items.destroy', $product) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-sm font-semibold text-red-700 hover:text-red-900">حذف</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </article>
                    @endforeach
                </div>

                <aside class="self-start rounded-lg border border-neutral-200 bg-white p-5">
                    <h2 class="text-xl font-semibold text-neutral-950">خلاصه سفارش</h2>
                    <div class="mt-5 flex items-center justify-between border-b border-neutral-200 pb-4">
                        <span class="text-neutral-600">جمع کل</span>
                        <span class="font-semibold text-neutral-950">{{ \App\Support\Storefront\PriceFormatter::format($subtotal) }}</span>
                    </div>
                    <a
                        href="{{ route('storefront.checkout.show') }}"
                        class="mt-5 inline-flex min-h-12 w-full items-center justify-center rounded-md bg-neutral-950 px-4 text-sm font-semibold text-white hover:bg-red-700"
                    >
                        ادامه ثبت سفارش
                    </a>
                </aside>
            </div>
        @endif
    </section>
@endsection
