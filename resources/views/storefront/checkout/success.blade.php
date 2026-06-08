@extends('storefront.layouts.app')

@section('content')
    <section class="bg-[#f6f3ef] py-12">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="rounded-lg border border-neutral-200 bg-white p-6">
                <p class="text-sm font-semibold text-emerald-700">سفارش ثبت شد</p>
                <h1 class="mt-3 text-3xl font-bold tracking-normal text-neutral-950">شماره سفارش {{ $order->order_number }}</h1>
                <p class="mt-3 leading-7 text-neutral-600">سفارش شما ثبت شد و برای پیگیری آماده است.</p>

                <div class="mt-6 grid gap-3 border-y border-neutral-200 py-5">
                    @foreach ($order->items as $item)
                        <div class="flex items-center justify-between gap-4">
                            <span class="font-medium text-neutral-950">{{ $item->title }} × {{ $item->quantity }}</span>
                            <span class="text-sm font-semibold text-neutral-700">{{ \App\Support\Storefront\PriceFormatter::format($item->line_total) }}</span>
                        </div>
                    @endforeach
                </div>

                <div class="mt-5 flex items-center justify-between">
                    <span class="text-neutral-600">جمع کل</span>
                    <span class="font-semibold text-neutral-950">{{ \App\Support\Storefront\PriceFormatter::format($order->total) }}</span>
                </div>

                <a href="{{ route('storefront.shop') }}" class="mt-6 inline-flex min-h-10 items-center rounded-md bg-neutral-950 px-4 text-sm font-semibold text-white hover:bg-red-700">
                    ادامه خرید
                </a>
            </div>
        </div>
    </section>
@endsection
