@extends('storefront.layouts.app')

@php
    $serviceOptions = $categories
        ->flatMap(fn ($category) => $category->offerings)
        ->pluck('title')
        ->unique()
        ->values();
@endphp

@section('content')
    <section class="bg-white">
        <div class="mx-auto grid max-w-7xl gap-8 px-4 py-10 sm:px-6 lg:grid-cols-[1.1fr_0.9fr] lg:px-8">
            <div class="self-center">
                <p class="text-sm font-semibold text-red-700">تعمیرگاه و سرویس</p>
                <h1 class="mt-3 text-3xl font-bold leading-tight tracking-normal text-neutral-950 sm:text-5xl">رزرو خدمات EtokBike</h1>
                <p class="mt-5 max-w-2xl leading-8 text-neutral-600">
                    سرویس دوره‌ای، تنظیم دنده و ترمز، عیب‌یابی و آماده‌سازی دوچرخه با ثبت مستقیم در پنل خدمات.
                </p>
                <div class="mt-6 flex flex-col gap-3 sm:flex-row">
                    <a href="#booking" class="inline-flex min-h-11 items-center justify-center rounded-md bg-neutral-950 px-5 text-sm font-semibold text-white hover:bg-red-700">ثبت درخواست سرویس</a>
                    <a href="{{ route('storefront.account') }}" class="inline-flex min-h-11 items-center justify-center rounded-md border border-neutral-300 bg-white px-5 text-sm font-semibold text-neutral-950 hover:border-red-700 hover:text-red-700">پیگیری سرویس</a>
                </div>
            </div>

            <div class="rounded-lg border border-neutral-200 bg-[#f6f3ef] p-5">
                <h2 class="text-xl font-semibold text-neutral-950">زمان‌های فعال</h2>
                <div class="mt-4 grid gap-3">
                    @forelse ($timeSlots as $slot)
                        <div class="flex items-center justify-between rounded-md border border-neutral-200 bg-white px-4 py-3">
                            <span class="font-medium text-neutral-950">{{ $slot->label }}</span>
                            <span class="text-sm font-semibold text-red-700">قابل انتخاب</span>
                        </div>
                    @empty
                        <p class="rounded-md border border-neutral-200 bg-white p-4 text-sm leading-6 text-neutral-600">زمان‌بندی فعال ثبت نشده است؛ درخواست شما برای هماهنگی دستی ارسال می‌شود.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </section>

    <section class="bg-[#f6f3ef] py-10">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
                <div>
                    <h2 class="text-2xl font-semibold tracking-normal text-neutral-950">خدمات قابل رزرو</h2>
                    <p class="mt-2 text-sm leading-6 text-neutral-600">هر خدمت از پنل مدیریت EtokBike به‌روز می‌شود.</p>
                </div>
            </div>

            <div class="mt-6 grid gap-6">
                @forelse ($categories as $category)
                    <section class="grid gap-4">
                        <h3 class="text-xl font-semibold text-neutral-950">{{ $category->title ?: $category->label }}</h3>
                        <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                            @foreach ($category->offerings as $offering)
                                @php
                                    $imageUrl = \App\Support\Mobile\ImageUrl::resolve($offering->image_url);
                                    $color = preg_match('/^#[0-9A-Fa-f]{3,8}$/', (string) $offering->thumbnail_color) ? $offering->thumbnail_color : '#101114';
                                @endphp
                                <article class="overflow-hidden rounded-lg border border-neutral-200 bg-white">
                                    @if ($imageUrl)
                                        <img src="{{ $imageUrl }}" alt="{{ $offering->title }}" class="aspect-[4/3] w-full object-cover" loading="lazy">
                                    @else
                                        <div class="grid aspect-[4/3] place-items-center text-white" style="background: linear-gradient(135deg, {{ $color }}, #171717);" role="img" aria-label="{{ $offering->title }}">
                                            <span class="text-3xl font-bold tracking-normal">{{ $offering->thumbnail_text }}</span>
                                        </div>
                                    @endif
                                    <div class="p-5">
                                        <p class="text-xs font-semibold text-red-700">{{ $category->label }}</p>
                                        <h4 class="mt-2 text-lg font-semibold text-neutral-950">{{ $offering->title }}</h4>
                                        <p class="mt-2 min-h-12 text-sm leading-6 text-neutral-600">{{ $offering->subtitle }}</p>
                                        @if ($offering->description)
                                            <p class="mt-3 text-sm leading-6 text-neutral-600">{{ $offering->description }}</p>
                                        @endif
                                        @if ($offering->price_label)
                                            <p class="mt-4 font-semibold text-neutral-950">{{ $offering->price_label }}</p>
                                        @endif
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    </section>
                @empty
                    <div class="rounded-lg border border-neutral-200 bg-white p-8 text-center">
                        <h2 class="text-xl font-semibold text-neutral-950">خدمتی ثبت نشده است</h2>
                        <p class="mt-2 text-neutral-600">بعد از فعال شدن خدمات در پنل مدیریت، این بخش به‌روزرسانی می‌شود.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    <section id="booking" class="bg-white py-10">
        <div class="mx-auto grid max-w-7xl gap-6 px-4 sm:px-6 lg:grid-cols-[0.9fr_1.1fr] lg:px-8">
            <div>
                <h2 class="text-2xl font-semibold tracking-normal text-neutral-950">ثبت درخواست سرویس</h2>
                <p class="mt-3 leading-7 text-neutral-600">درخواست در پنل خدمات ایجاد می‌شود و وضعیت آن از صفحه پیگیری قابل مشاهده است.</p>
                @if ($storeProfile)
                    <div class="mt-6 rounded-lg border border-neutral-200 bg-[#f6f3ef] p-5">
                        <p class="font-semibold text-neutral-950">{{ $storeProfile->branch_title }}</p>
                        <p class="mt-2 text-sm leading-6 text-neutral-600">{{ $storeProfile->address }}</p>
                        <p class="mt-1 text-sm leading-6 text-neutral-600">{{ $storeProfile->hours }}</p>
                    </div>
                @endif
            </div>

            <form method="POST" action="{{ route('storefront.services.bookings.store') }}" class="grid gap-5 rounded-lg border border-neutral-200 bg-[#f6f3ef] p-5">
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
                <div class="grid gap-5 sm:grid-cols-3">
                    <label class="grid gap-2 text-sm font-medium text-neutral-800 sm:col-span-1">
                        نوع سرویس
                        <select name="service_type" required class="min-h-11 rounded-md border border-neutral-300 bg-white px-3 text-neutral-950">
                            @foreach ($serviceOptions as $title)
                                <option value="{{ $title }}" @selected(old('service_type') === $title)>{{ $title }}</option>
                            @endforeach
                            @if ($serviceOptions->isEmpty())
                                <option value="درخواست عمومی">درخواست عمومی</option>
                            @endif
                        </select>
                        @error('service_type') <span class="text-xs text-red-700">{{ $message }}</span> @enderror
                    </label>
                    <label class="grid gap-2 text-sm font-medium text-neutral-800">
                        دوچرخه
                        <input name="bike_label" value="{{ old('bike_label') }}" placeholder="مثلا ETX 200" class="min-h-11 rounded-md border border-neutral-300 px-3 text-neutral-950">
                        @error('bike_label') <span class="text-xs text-red-700">{{ $message }}</span> @enderror
                    </label>
                    <label class="grid gap-2 text-sm font-medium text-neutral-800">
                        زمان پیشنهادی
                        <select name="preferred_time" class="min-h-11 rounded-md border border-neutral-300 bg-white px-3 text-neutral-950">
                            <option value="">هماهنگی با پشتیبانی</option>
                            @foreach ($timeSlots as $slot)
                                <option value="{{ $slot->label }}" @selected(old('preferred_time') === $slot->label)>{{ $slot->label }}</option>
                            @endforeach
                        </select>
                        @error('preferred_time') <span class="text-xs text-red-700">{{ $message }}</span> @enderror
                    </label>
                </div>
                <label class="grid gap-2 text-sm font-medium text-neutral-800">
                    توضیح مشکل
                    <textarea name="problem_description" rows="5" class="rounded-md border border-neutral-300 px-3 py-2 text-neutral-950">{{ old('problem_description') }}</textarea>
                    @error('problem_description') <span class="text-xs text-red-700">{{ $message }}</span> @enderror
                </label>
                <button type="submit" class="min-h-12 rounded-md bg-neutral-950 px-6 text-sm font-semibold text-white hover:bg-red-700">ثبت درخواست سرویس</button>
            </form>
        </div>
    </section>
@endsection
