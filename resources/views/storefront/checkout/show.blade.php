@extends('storefront.layouts.app')

@section('content')
    <section class="bg-white">
        <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <h1 class="text-3xl font-bold tracking-normal text-neutral-950">تکمیل سفارش</h1>
        </div>
    </section>

    <section class="bg-[#f6f3ef] py-8">
        <div class="mx-auto grid max-w-7xl gap-6 px-4 sm:px-6 lg:grid-cols-[1fr_380px] lg:px-8">
            <form method="POST" action="{{ route('storefront.checkout.store') }}" class="grid gap-5 rounded-lg border border-neutral-200 bg-white p-5">
                @csrf
                <div class="grid gap-5 sm:grid-cols-2">
                    <label class="grid gap-2 text-sm font-medium text-neutral-800">
                        نام و نام خانوادگی
                        <input name="customer_name" value="{{ old('customer_name') }}" required class="min-h-11 rounded-md border border-neutral-300 px-3 text-neutral-950">
                        @error('customer_name') <span class="text-xs text-red-700">{{ $message }}</span> @enderror
                    </label>
                    <label class="grid gap-2 text-sm font-medium text-neutral-800">
                        شماره تماس
                        <input name="customer_phone" value="{{ old('customer_phone') }}" required class="min-h-11 rounded-md border border-neutral-300 px-3 text-neutral-950">
                        @error('customer_phone') <span class="text-xs text-red-700">{{ $message }}</span> @enderror
                    </label>
                </div>

                <label class="grid gap-2 text-sm font-medium text-neutral-800">
                    ایمیل
                    <input name="customer_email" type="email" value="{{ old('customer_email') }}" class="min-h-11 rounded-md border border-neutral-300 px-3 text-neutral-950">
                    @error('customer_email') <span class="text-xs text-red-700">{{ $message }}</span> @enderror
                </label>

                <fieldset class="grid gap-3">
                    <legend class="text-sm font-semibold text-neutral-950">روش دریافت</legend>
                    <label class="flex items-center gap-3 rounded-md border border-neutral-300 p-3 text-sm font-medium">
                        <input type="radio" name="fulfillment_method" value="pickup" @checked(old('fulfillment_method', 'pickup') === 'pickup')>
                        تحویل حضوری
                    </label>
                    <label class="flex items-center gap-3 rounded-md border border-neutral-300 p-3 text-sm font-medium">
                        <input type="radio" name="fulfillment_method" value="delivery" @checked(old('fulfillment_method') === 'delivery')>
                        ارسال
                    </label>
                    @error('fulfillment_method') <span class="text-xs text-red-700">{{ $message }}</span> @enderror
                </fieldset>

                <label class="grid gap-2 text-sm font-medium text-neutral-800">
                    توضیحات سفارش
                    <textarea name="customer_notes" rows="5" class="rounded-md border border-neutral-300 px-3 py-2 text-neutral-950">{{ old('customer_notes') }}</textarea>
                    @error('customer_notes') <span class="text-xs text-red-700">{{ $message }}</span> @enderror
                </label>

                <button type="submit" class="min-h-12 rounded-md bg-neutral-950 px-6 text-sm font-semibold text-white hover:bg-red-700">
                    ثبت سفارش
                </button>
            </form>

            <aside class="self-start rounded-lg border border-neutral-200 bg-white p-5">
                <h2 class="text-xl font-semibold text-neutral-950">سفارش شما</h2>
                <div class="mt-5 grid gap-4">
                    @foreach ($lines as $line)
                        <div class="flex items-start justify-between gap-3 border-b border-neutral-200 pb-3">
                            <div>
                                <p class="font-medium text-neutral-950">{{ $line['product']->title }}</p>
                                <p class="mt-1 text-sm text-neutral-600">تعداد: {{ $line['quantity'] }}</p>
                            </div>
                            <p class="text-sm font-semibold text-neutral-950">{{ \App\Support\Storefront\PriceFormatter::format($line['line_total']) }}</p>
                        </div>
                    @endforeach
                </div>
                <div class="mt-5 flex items-center justify-between">
                    <span class="text-neutral-600">جمع کل</span>
                    <span class="font-semibold text-neutral-950">{{ \App\Support\Storefront\PriceFormatter::format($subtotal) }}</span>
                </div>
            </aside>
        </div>
    </section>
@endsection
