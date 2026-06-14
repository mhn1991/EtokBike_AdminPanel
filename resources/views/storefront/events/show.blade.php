@extends('storefront.layouts.app')

@php
    $imageUrl = \App\Support\Mobile\ImageUrl::resolve($program->image_url);
    $color = preg_match('/^#[0-9A-Fa-f]{3,8}$/', (string) $program->thumbnail_color) ? $program->thumbnail_color : '#101114';
    $remaining = $program->capacity === null ? null : max(0, $program->capacity - $program->reserved_count);
@endphp

@section('content')
    <section class="bg-white">
        <div class="mx-auto grid max-w-7xl gap-8 px-4 py-8 sm:px-6 lg:grid-cols-2 lg:px-8">
            <div class="grid gap-5">
                @include('storefront.partials.breadcrumbs', ['items' => [
                    ['name' => 'EtokBike', 'url' => route('storefront.home')],
                    ['name' => 'برنامه‌ها', 'url' => route('storefront.events')],
                    ['name' => $program->title, 'url' => route('storefront.events.show', $program)],
                ]])
                @if ($imageUrl)
                    <img src="{{ $imageUrl }}" alt="{{ $program->title }}" class="aspect-[4/3] w-full rounded-md object-cover" loading="eager">
                @else
                    <div class="grid aspect-[4/3] w-full place-items-center rounded-md text-white" style="background: linear-gradient(135deg, {{ $color }}, #171717);" role="img" aria-label="{{ $program->title }}">
                        <span class="text-4xl font-bold tracking-normal">{{ $program->thumbnail_text }}</span>
                    </div>
                @endif
            </div>

            <article class="self-center">
                <p class="text-sm font-semibold text-red-700">{{ $program->category?->label }}</p>
                <h1 class="mt-3 text-3xl font-bold leading-tight tracking-normal text-neutral-950 sm:text-4xl">{{ $program->title }}</h1>
                <p class="mt-4 text-lg leading-8 text-neutral-700">{{ $program->subtitle }}</p>
                @if ($program->advertisement)
                    <p class="mt-4 leading-8 text-neutral-600">{{ $program->advertisement }}</p>
                @endif

                <dl class="mt-6 grid gap-3 border-y border-neutral-200 py-5 sm:grid-cols-2">
                    <div>
                        <dt class="text-sm text-neutral-500">زمان برنامه</dt>
                        <dd class="mt-1 font-semibold text-neutral-950">{{ $program->date_label }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-neutral-500">وضعیت</dt>
                        <dd class="mt-1 font-semibold text-neutral-950">{{ $program->status_label }}</dd>
                    </div>
                    @if ($remaining !== null)
                        <div>
                            <dt class="text-sm text-neutral-500">ظرفیت باقی‌مانده</dt>
                            <dd class="mt-1 font-semibold text-neutral-950">{{ $remaining }}</dd>
                        </div>
                    @endif
                </dl>

                @if (! empty($program->details))
                    <div class="mt-5 flex flex-wrap gap-2">
                        @foreach ($program->details as $detail)
                            <span class="rounded-md bg-neutral-100 px-3 py-2 text-sm font-medium text-neutral-800">{{ $detail }}</span>
                        @endforeach
                    </div>
                @endif
            </article>
        </div>
    </section>

    @if ($program->program_state === 'future')
        <section class="bg-[#f6f3ef] py-10">
            <div class="mx-auto grid max-w-7xl gap-6 px-4 sm:px-6 lg:grid-cols-[0.85fr_1.15fr] lg:px-8">
                <div>
                    <h2 class="text-2xl font-semibold tracking-normal text-neutral-950">{{ $program->book_label ?: 'رزرو برنامه' }}</h2>
                    <p class="mt-3 leading-7 text-neutral-600">رزرو بعد از ثبت در وضعیت بررسی قرار می‌گیرد و از صفحه پیگیری قابل مشاهده است.</p>
                </div>

                <form method="POST" action="{{ route('storefront.events.bookings.store', $program) }}" class="grid gap-5 rounded-lg border border-neutral-200 bg-white p-5">
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
                    <div class="grid gap-5 sm:grid-cols-[1fr_140px]">
                        <label class="grid gap-2 text-sm font-medium text-neutral-800">
                            ایمیل
                            <input name="customer_email" type="email" value="{{ old('customer_email') }}" class="min-h-11 rounded-md border border-neutral-300 px-3 text-neutral-950">
                            @error('customer_email') <span class="text-xs text-red-700">{{ $message }}</span> @enderror
                        </label>
                        <label class="grid gap-2 text-sm font-medium text-neutral-800">
                            تعداد نفرات
                            <input name="attendees" type="number" min="1" max="20" value="{{ old('attendees', 1) }}" class="min-h-11 rounded-md border border-neutral-300 px-3 text-neutral-950">
                            @error('attendees') <span class="text-xs text-red-700">{{ $message }}</span> @enderror
                        </label>
                    </div>
                    <label class="grid gap-2 text-sm font-medium text-neutral-800">
                        توضیحات
                        <textarea name="customer_notes" rows="4" class="rounded-md border border-neutral-300 px-3 py-2 text-neutral-950">{{ old('customer_notes') }}</textarea>
                        @error('customer_notes') <span class="text-xs text-red-700">{{ $message }}</span> @enderror
                    </label>
                    <button type="submit" class="min-h-12 rounded-md bg-neutral-950 px-6 text-sm font-semibold text-white hover:bg-red-700">ثبت رزرو</button>
                </form>
            </div>
        </section>
    @endif

    @if ($program->program_state === 'finished' && $program->galleryItems->isNotEmpty())
        <section class="bg-[#f6f3ef] py-10">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <h2 class="text-2xl font-semibold tracking-normal text-neutral-950">{{ $program->gallery_title ?: 'گالری برنامه' }}</h2>
                <div class="mt-6 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($program->galleryItems->sortBy('sort_order') as $item)
                        @php
                            $galleryImage = \App\Support\Mobile\ImageUrl::resolve($item->image_url);
                            $galleryColor = preg_match('/^#[0-9A-Fa-f]{3,8}$/', (string) $item->thumbnail_color) ? $item->thumbnail_color : '#101114';
                        @endphp
                        <figure class="overflow-hidden rounded-lg border border-neutral-200 bg-white">
                            @if ($galleryImage)
                                <img src="{{ $galleryImage }}" alt="{{ $item->caption ?: $program->title }}" class="aspect-[4/3] w-full object-cover" loading="lazy">
                            @else
                                <div class="grid aspect-[4/3] place-items-center text-white" style="background: linear-gradient(135deg, {{ $galleryColor }}, #171717);" role="img" aria-label="{{ $item->caption ?: $program->title }}">
                                    <span class="text-3xl font-bold tracking-normal">{{ $item->thumbnail_text }}</span>
                                </div>
                            @endif
                            @if ($item->caption)
                                <figcaption class="p-4 text-sm leading-6 text-neutral-700">{{ $item->caption }}</figcaption>
                            @endif
                        </figure>
                    @endforeach
                </div>
            </div>
        </section>
    @endif
@endsection
