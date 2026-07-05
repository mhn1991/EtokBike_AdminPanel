@extends('storefront.layouts.app')

@section('content')
    <section class="bg-surface">
        <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <h1 class="text-3xl font-bold tracking-normal text-ink">سبد خرید</h1>
        </div>
    </section>

    <section class="bg-surface-page py-8">
        @if ($lines->isEmpty())
            <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
                <div class="rounded-2xl border border-border bg-surface p-8 text-center shadow-sm">
                    <p class="text-sm font-semibold text-brand">سبد خرید</p>
                    <h2 class="mt-3 text-2xl font-bold text-ink">سبد خرید خالی است</h2>
                    <p class="mx-auto mt-3 max-w-md leading-7 text-muted">برای ثبت سفارش، ابتدا یک دوچرخه، قطعه یا لوازم جانبی انتخاب کنید.</p>
                    <div class="mt-6 flex flex-col justify-center gap-3 sm:flex-row">
                        <a href="{{ route('storefront.shop') }}" class="inline-flex min-h-11 items-center justify-center rounded-xl bg-brand px-5 text-sm font-semibold text-white transition hover:bg-brand-hover">رفتن به فروشگاه</a>
                        <a href="{{ route('storefront.home') }}" class="inline-flex min-h-11 items-center justify-center rounded-xl border border-border bg-surface px-5 text-sm font-semibold text-ink transition hover:border-brand hover:text-brand">بازگشت به خانه</a>
                    </div>
                </div>
            </div>
        @else
            <div class="mx-auto grid max-w-7xl gap-6 px-4 sm:px-6 lg:grid-cols-[1fr_360px] lg:px-8">
                <div class="grid gap-4">
                    @foreach ($lines as $line)
                    @php($product = $line['product'])
                    <article class="grid gap-4 rounded-2xl border border-border bg-surface p-4 shadow-sm sm:grid-cols-[140px_1fr]">
                        @include('storefront.partials.product-visual', ['product' => $product, 'class' => 'aspect-[4/3] sm:aspect-square', 'loading' => 'lazy'])
                        <div class="grid gap-4">
                            <div>
                                <h2 class="text-lg font-semibold text-ink">
                                    <a href="{{ route('storefront.products.show', $product) }}" class="transition hover:text-brand">{{ $product->title }}</a>
                                </h2>
                                <p class="mt-1 text-sm text-muted">{{ $product->subtitle }}</p>
                            </div>
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                                <form method="POST" action="{{ route('storefront.cart.items.update', $product) }}" class="flex items-end gap-2">
                                    @csrf
                                    @method('PATCH')
                                    <label class="grid gap-1 text-sm font-medium text-ink">
                                        تعداد
                                        <input name="quantity" type="number" min="1" max="20" value="{{ $line['quantity'] }}" class="storefront-control h-10 w-24 px-3">
                                    </label>
                                    <button type="submit" class="h-10 rounded-xl border border-border bg-surface px-3 text-sm font-semibold transition hover:border-brand hover:text-brand">به‌روزرسانی</button>
                                </form>
                                <div class="flex items-center justify-between gap-4">
                                    <p class="font-semibold text-ink">{{ \App\Support\Storefront\PriceFormatter::format($line['line_total']) }}</p>
                                    <form method="POST" action="{{ route('storefront.cart.items.destroy', $product) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-sm font-semibold text-brand transition hover:text-brand-hover">حذف</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </article>
                    @endforeach
                </div>

                <aside class="self-start rounded-2xl border border-border bg-surface p-5 shadow-sm">
                    <h2 class="text-xl font-semibold text-ink">خلاصه سفارش</h2>
                    <div class="mt-5 flex items-center justify-between border-b border-border pb-4">
                        <span class="text-muted">جمع کل</span>
                        <span class="font-semibold text-ink">{{ \App\Support\Storefront\PriceFormatter::format($subtotal) }}</span>
                    </div>
                    <a
                        href="{{ route('storefront.checkout.show') }}"
                        class="mt-5 inline-flex min-h-12 w-full items-center justify-center rounded-xl bg-brand px-4 text-sm font-semibold text-white transition hover:bg-brand-hover"
                    >
                        ادامه ثبت سفارش
                    </a>
                </aside>
            </div>
        @endif
    </section>
@endsection
