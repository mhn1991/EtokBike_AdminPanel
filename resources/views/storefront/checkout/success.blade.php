@extends('storefront.layouts.app')

@section('content')
    <section class="bg-surface-page py-12">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="rounded-2xl border border-border bg-surface p-6 shadow-sm">
                <p class="text-sm font-semibold text-emerald-700">سفارش ثبت شد</p>
                <h1 class="mt-3 text-3xl font-bold tracking-normal text-ink">شماره سفارش {{ $order->order_number }}</h1>
                <p class="mt-3 leading-7 text-muted">سفارش شما ثبت شد و برای پیگیری آماده است.</p>

                <div class="mt-6 grid gap-3 border-y border-border py-5">
                    @foreach ($order->items as $item)
                        <div class="flex items-center justify-between gap-4">
                            <span class="font-medium text-ink">{{ $item->title }} × {{ $item->quantity }}</span>
                            <span class="text-sm font-semibold text-muted">{{ \App\Support\Storefront\PriceFormatter::format($item->line_total) }}</span>
                        </div>
                    @endforeach
                </div>

                <div class="mt-5 flex items-center justify-between">
                    <span class="text-muted">جمع کل</span>
                    <span class="font-semibold text-ink">{{ \App\Support\Storefront\PriceFormatter::format($order->total) }}</span>
                </div>

                @if ($order->discount_total > 0 || $order->delivery_total > 0)
                    <div class="mt-3 grid gap-2 text-sm text-muted">
                        @if ($order->discount_total > 0)
                            <div class="flex items-center justify-between">
                                <span>تخفیف</span>
                                <span>{{ \App\Support\Storefront\PriceFormatter::format($order->discount_total) }}</span>
                            </div>
                        @endif
                        @if ($order->delivery_total > 0)
                            <div class="flex items-center justify-between">
                                <span>هزینه ارسال</span>
                                <span>{{ \App\Support\Storefront\PriceFormatter::format($order->delivery_total) }}</span>
                            </div>
                        @endif
                    </div>
                @endif

                <div class="mt-6 flex flex-col gap-3 sm:flex-row">
                    <a href="{{ route('storefront.account', ['order_number' => $order->order_number]) }}" class="inline-flex min-h-11 items-center justify-center rounded-xl bg-brand px-4 text-sm font-semibold text-white transition hover:bg-brand-hover">
                        پیگیری سفارش
                    </a>
                    <a href="{{ route('storefront.shop') }}" class="inline-flex min-h-11 items-center justify-center rounded-xl border border-border bg-surface px-4 text-sm font-semibold text-ink transition hover:border-brand hover:text-brand">
                        ادامه خرید
                    </a>
                </div>
            </div>
        </div>
    </section>
@endsection
