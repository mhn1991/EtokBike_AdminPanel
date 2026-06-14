@extends('storefront.layouts.app')

@section('content')
    <section class="bg-white">
        <div class="mx-auto grid max-w-7xl gap-8 px-4 py-10 sm:px-6 lg:grid-cols-[1fr_0.8fr] lg:px-8">
            <div>
                <p class="text-sm font-semibold text-red-700">برنامه‌ها و رویدادها</p>
                <h1 class="mt-3 text-3xl font-bold leading-tight tracking-normal text-neutral-950 sm:text-5xl">برنامه‌های EtokBike</h1>
                <p class="mt-5 max-w-2xl leading-8 text-neutral-600">برنامه‌های آینده را رزرو کنید و گالری برنامه‌های برگزار شده را ببینید.</p>
            </div>
            <div class="rounded-lg border border-neutral-200 bg-[#f6f3ef] p-5">
                <h2 class="text-xl font-semibold text-neutral-950">رزرو برنامه</h2>
                <p class="mt-3 text-sm leading-6 text-neutral-600">رزروها مستقیم در پنل برنامه‌ها ثبت می‌شوند و ظرفیت باقی‌مانده هنگام ثبت بررسی می‌شود.</p>
                <a href="#programs" class="mt-5 inline-flex min-h-11 items-center rounded-md bg-neutral-950 px-5 text-sm font-semibold text-white hover:bg-red-700">دیدن برنامه‌ها</a>
            </div>
        </div>
    </section>

    <section id="programs" class="bg-[#f6f3ef] py-10">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid gap-8">
                @forelse ($categories as $category)
                    <section class="grid gap-4">
                        <div>
                            <p class="text-sm font-semibold text-red-700">{{ $category->label }}</p>
                            <h2 class="mt-1 text-2xl font-semibold tracking-normal text-neutral-950">{{ $category->title ?: $category->label }}</h2>
                        </div>

                        <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                            @foreach ($category->programs as $program)
                                @php
                                    $imageUrl = \App\Support\Mobile\ImageUrl::resolve($program->image_url);
                                    $color = preg_match('/^#[0-9A-Fa-f]{3,8}$/', (string) $program->thumbnail_color) ? $program->thumbnail_color : '#101114';
                                    $remaining = $program->capacity === null ? null : max(0, $program->capacity - $program->reserved_count);
                                @endphp
                                <article class="overflow-hidden rounded-lg border border-neutral-200 bg-white">
                                    <a href="{{ route('storefront.events.show', $program) }}" class="block">
                                        @if ($imageUrl)
                                            <img src="{{ $imageUrl }}" alt="{{ $program->title }}" class="aspect-[4/3] w-full object-cover" loading="lazy">
                                        @else
                                            <div class="grid aspect-[4/3] place-items-center text-white" style="background: linear-gradient(135deg, {{ $color }}, #171717);" role="img" aria-label="{{ $program->title }}">
                                                <span class="text-3xl font-bold tracking-normal">{{ $program->thumbnail_text }}</span>
                                            </div>
                                        @endif
                                    </a>
                                    <div class="p-5">
                                        <div class="flex flex-wrap items-center gap-2 text-xs font-semibold">
                                            <span class="rounded-md bg-red-50 px-2 py-1 text-red-700">{{ $program->status_label }}</span>
                                            @if ($remaining !== null)
                                                <span class="rounded-md bg-neutral-100 px-2 py-1 text-neutral-700">{{ $remaining }} ظرفیت باقی‌مانده</span>
                                            @endif
                                        </div>
                                        <h3 class="mt-3 text-lg font-semibold text-neutral-950">
                                            <a href="{{ route('storefront.events.show', $program) }}" class="hover:text-red-700">{{ $program->title }}</a>
                                        </h3>
                                        <p class="mt-2 text-sm leading-6 text-neutral-600">{{ $program->subtitle }}</p>
                                        <p class="mt-3 text-sm font-semibold text-neutral-950">{{ $program->date_label }}</p>
                                        <a href="{{ route('storefront.events.show', $program) }}" class="mt-5 inline-flex min-h-10 w-full items-center justify-center rounded-md border border-neutral-300 bg-white px-4 text-sm font-semibold text-neutral-950 hover:border-red-700 hover:text-red-700">
                                            {{ $program->program_state === 'future' ? ($program->book_label ?: 'رزرو برنامه') : ($program->view_label ?: 'مشاهده برنامه') }}
                                        </a>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    </section>
                @empty
                    <div class="rounded-lg border border-neutral-200 bg-white p-8 text-center">
                        <h2 class="text-xl font-semibold text-neutral-950">برنامه‌ای ثبت نشده است</h2>
                        <p class="mt-2 text-neutral-600">بعد از فعال شدن برنامه‌ها در پنل مدیریت، این صفحه به‌روزرسانی می‌شود.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </section>
@endsection
