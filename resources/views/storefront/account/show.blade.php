@extends('storefront.layouts.app')

@php
    $orderStatusLabels = [
        'pending' => 'در انتظار بررسی',
        'confirmed' => 'تایید شده',
        'processing' => 'در حال آماده‌سازی',
        'ready' => 'آماده تحویل',
        'completed' => 'تکمیل شده',
        'cancelled' => 'لغو شده',
    ];
    $paymentStatusLabels = [
        'unpaid' => 'پرداخت نشده',
        'paid' => 'پرداخت شده',
        'refunded' => 'بازپرداخت شده',
        'failed' => 'ناموفق',
    ];
    $serviceStatusLabels = [
        'pending' => 'در انتظار تایید',
        'confirmed' => 'زمان تایید شده',
        'in_progress' => 'در حال انجام',
        'completed' => 'تکمیل شده',
        'cancelled' => 'لغو شده',
    ];
    $hasResults = $profile || $orders->isNotEmpty() || $serviceBookings->isNotEmpty() || $programBookings->isNotEmpty();
@endphp

@section('content')
    <section class="bg-surface">
        <div class="mx-auto grid max-w-7xl gap-8 px-4 py-10 sm:px-6 lg:grid-cols-[0.9fr_1.1fr] lg:px-8">
            <div>
                <p class="inline-flex rounded-full bg-brand-soft px-3 py-1 text-sm font-semibold text-brand">پیگیری مشتری</p>
                <h1 class="mt-4 text-3xl font-bold leading-tight tracking-normal text-ink sm:text-5xl">حساب و وضعیت‌ها</h1>
                <p class="mt-5 leading-8 text-muted">با شماره تماس، ایمیل یا شماره سفارش، سفارش‌ها، سرویس‌ها، رزرو برنامه و مشخصات دوچرخه را ببینید.</p>
            </div>

            <form method="GET" action="{{ route('storefront.account') }}" class="grid gap-5 rounded-2xl border border-border bg-surface-page p-5 shadow-sm">
                <div class="grid gap-5 sm:grid-cols-2">
                    <label class="grid gap-2 text-sm font-medium text-neutral-800">
                        شماره تماس
                        <input name="phone" value="{{ old('phone', $lookup['phone']) }}" class="storefront-control px-3">
                        @error('phone') <span class="text-xs text-red-700">{{ $message }}</span> @enderror
                    </label>
                    <label class="grid gap-2 text-sm font-medium text-neutral-800">
                        ایمیل
                        <input name="email" type="email" value="{{ old('email', $lookup['email']) }}" class="storefront-control px-3">
                        @error('email') <span class="text-xs text-red-700">{{ $message }}</span> @enderror
                    </label>
                </div>
                <label class="grid gap-2 text-sm font-medium text-neutral-800">
                    شماره سفارش
                    <input name="order_number" value="{{ old('order_number', $lookup['order_number']) }}" class="storefront-control px-3">
                    @error('order_number') <span class="text-xs text-red-700">{{ $message }}</span> @enderror
                </label>
                <button type="submit" class="min-h-12 rounded-xl bg-brand px-6 text-sm font-semibold text-white transition hover:bg-brand-hover">نمایش وضعیت</button>
            </form>
        </div>
    </section>

    <section class="bg-surface-page py-10">
        <div class="mx-auto grid max-w-7xl gap-6 px-4 sm:px-6 lg:px-8">
            @if ($hasLookup && ! $hasResults)
                <div class="rounded-2xl border border-border bg-surface p-8 text-center">
                    <h2 class="text-xl font-semibold text-ink">موردی پیدا نشد</h2>
                    <p class="mt-2 text-muted">اطلاعات وارد شده را بررسی کنید یا از صفحه پیام با پشتیبانی تماس بگیرید.</p>
                    <a href="{{ route('storefront.messages') }}" class="mt-5 inline-flex min-h-11 items-center rounded-xl bg-brand px-4 text-sm font-semibold text-white transition hover:bg-brand-hover">ارسال پیام</a>
                </div>
            @elseif (! $hasLookup)
                <div class="rounded-2xl border border-border bg-surface p-8 text-center">
                    <h2 class="text-xl font-semibold text-ink">اطلاعات پیگیری را وارد کنید</h2>
                    <p class="mt-2 text-muted">نتایج حساب بعد از جستجو در همین صفحه نمایش داده می‌شود.</p>
                </div>
            @else
                @if ($profile)
                    <section class="rounded-2xl border border-border bg-surface p-5 shadow-sm">
                        <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-start">
                            <div>
                                <p class="text-sm font-semibold text-brand">پروفایل مشتری</p>
                                <h2 class="mt-2 text-2xl font-semibold tracking-normal text-ink">{{ $profile->name }}</h2>
                            </div>
                            <div class="grid gap-1 text-sm text-muted sm:text-left">
                                @if ($profile->phone)<p>{{ $profile->phone }}</p>@endif
                                @if ($profile->email)<p>{{ $profile->email }}</p>@endif
                            </div>
                        </div>
                        @if ($profile->delivery_address)
                            <p class="mt-4 rounded-xl bg-surface-page p-4 text-sm leading-6 text-muted">{{ $profile->delivery_address }}</p>
                        @endif
                    </section>

                    @if ($profile->bikeProfiles->isNotEmpty())
                        <section>
                            <h2 class="text-2xl font-semibold tracking-normal text-ink">دوچرخه‌ها</h2>
                            <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                                @foreach ($profile->bikeProfiles as $bike)
                                    <article class="rounded-2xl border border-border bg-surface p-5 shadow-sm">
                                        <h3 class="text-lg font-semibold text-ink">{{ $bike->title }}</h3>
                                        <p class="mt-2 text-sm leading-6 text-muted">{{ $bike->subtitle }}</p>
                                        <dl class="mt-4 grid gap-2 text-sm">
                                            @foreach ($bike->toMobilePayload()['fields'] as $field)
                                                <div class="flex justify-between gap-3 border-t border-neutral-100 pt-2">
                                                    <dt class="text-muted">{{ $field['label'] }}</dt>
                                                    <dd class="font-medium text-ink">{{ $field['value'] }}</dd>
                                                </div>
                                            @endforeach
                                        </dl>
                                    </article>
                                @endforeach
                            </div>
                        </section>
                    @endif
                @endif

                @if ($orders->isNotEmpty())
                    <section>
                        <h2 class="text-2xl font-semibold tracking-normal text-ink">سفارش‌ها</h2>
                        <div class="mt-4 grid gap-4">
                            @foreach ($orders as $order)
                                <article class="rounded-2xl border border-border bg-surface p-5 shadow-sm">
                                    <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-start">
                                        <div>
                                            <p class="text-sm font-semibold text-brand">{{ $order->order_number }}</p>
                                            <h3 class="mt-1 text-lg font-semibold text-ink">{{ $orderStatusLabels[$order->status] ?? $order->status }}</h3>
                                            <p class="mt-1 text-sm text-muted">پرداخت: {{ $paymentStatusLabels[$order->payment_status] ?? $order->payment_status }}</p>
                                        </div>
                                        <p class="font-semibold text-ink">{{ \App\Support\Storefront\PriceFormatter::format($order->total) }}</p>
                                    </div>

                                    <div class="mt-4 grid gap-3 border-y border-border py-4">
                                        @foreach ($order->items as $item)
                                            <div class="flex justify-between gap-4 text-sm">
                                                <span class="font-medium text-ink">{{ $item->title }} × {{ $item->quantity }}</span>
                                                <span class="text-muted">{{ \App\Support\Storefront\PriceFormatter::format($item->line_total) }}</span>
                                            </div>
                                        @endforeach
                                    </div>

                                    @if ($order->shipments->isNotEmpty() || $order->receipts->isNotEmpty() || $order->returnRequests->isNotEmpty())
                                        <div class="mt-4 grid gap-3 sm:grid-cols-3">
                                            @foreach ($order->shipments as $shipment)
                                                <div class="rounded-xl bg-surface-page p-3 text-sm">
                                                    <p class="font-semibold text-ink">ارسال: {{ $shipment->status }}</p>
                                                    @if ($shipment->tracking_number)<p class="mt-1 text-muted">رهگیری: {{ $shipment->tracking_number }}</p>@endif
                                                </div>
                                            @endforeach
                                            @foreach ($order->receipts as $receipt)
                                                <div class="rounded-xl bg-surface-page p-3 text-sm">
                                                    <p class="font-semibold text-ink">رسید: {{ $receipt->receipt_number }}</p>
                                                    <p class="mt-1 text-muted">{{ $receipt->status }}</p>
                                                </div>
                                            @endforeach
                                            @foreach ($order->returnRequests as $return)
                                                <div class="rounded-xl bg-surface-page p-3 text-sm">
                                                    <p class="font-semibold text-ink">مرجوعی: {{ $return->status }}</p>
                                                    <p class="mt-1 text-muted">{{ $return->refund_status }}</p>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </article>
                            @endforeach
                        </div>
                    </section>
                @endif

                @if ($serviceBookings->isNotEmpty())
                    <section>
                        <h2 class="text-2xl font-semibold tracking-normal text-ink">سرویس‌ها</h2>
                        <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                            @foreach ($serviceBookings as $booking)
                                <article class="rounded-2xl border border-border bg-surface p-5 shadow-sm">
                                    <p class="text-sm font-semibold text-brand">{{ $serviceStatusLabels[$booking->status] ?? $booking->status }}</p>
                                    <h3 class="mt-2 text-lg font-semibold text-ink">{{ $booking->service_type }}</h3>
                                    <p class="mt-2 text-sm leading-6 text-muted">{{ $booking->bike_label ?: $booking->customer_name }}</p>
                                    @if ($booking->preferred_time)
                                        <p class="mt-3 text-sm font-medium text-ink">{{ $booking->preferred_time }}</p>
                                    @endif
                                </article>
                            @endforeach
                        </div>
                    </section>
                @endif

                @if ($programBookings->isNotEmpty())
                    <section>
                        <h2 class="text-2xl font-semibold tracking-normal text-ink">رزرو برنامه‌ها</h2>
                        <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                            @foreach ($programBookings as $booking)
                                <article class="rounded-2xl border border-border bg-surface p-5 shadow-sm">
                                    <p class="text-sm font-semibold text-brand">{{ $booking->status }}</p>
                                    <h3 class="mt-2 text-lg font-semibold text-ink">{{ $booking->program?->title ?: 'برنامه' }}</h3>
                                    <p class="mt-2 text-sm leading-6 text-muted">تعداد نفرات: {{ $booking->attendees }}</p>
                                    @if ($booking->program)
                                        <a href="{{ route('storefront.events.show', $booking->program) }}" class="mt-4 inline-flex min-h-11 items-center rounded-xl border border-border px-4 text-sm font-semibold transition hover:border-brand hover:text-brand">مشاهده برنامه</a>
                                    @endif
                                </article>
                            @endforeach
                        </div>
                    </section>
                @endif
            @endif
        </div>
    </section>
@endsection
