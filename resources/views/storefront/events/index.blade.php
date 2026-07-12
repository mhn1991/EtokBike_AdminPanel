@extends('storefront.layouts.app')

@section('content')
    <section class="storefront-page-hero">
        <div class="mx-auto grid max-w-7xl gap-8 px-4 py-10 sm:px-6 lg:grid-cols-[1fr_0.8fr] lg:px-8">
            <div>
                <p class="storefront-eyebrow">برنامه‌ها و رویدادها</p>
                <h1 class="mt-4 text-3xl font-extrabold leading-tight tracking-normal text-ink sm:text-5xl">بیرون از فروشگاه هم همراهتان هستیم</h1>
                <p class="mt-5 max-w-2xl leading-8 text-muted">برنامه‌های آینده را رزرو کنید و گالری برنامه‌های برگزار شده را ببینید.</p>
            </div>
            <div class="storefront-surface-card p-5 sm:p-6">
                <p class="storefront-eyebrow">رکاب‌زنی گروهی</p>
                <h2 class="mt-3 text-xl font-bold text-ink">رزرو برنامه</h2>
                <p class="mt-3 text-sm leading-6 text-muted">رزروها مستقیم در پنل برنامه‌ها ثبت می‌شوند و ظرفیت باقی‌مانده هنگام ثبت بررسی می‌شود.</p>
                <a href="#programs" class="mt-5 inline-flex min-h-11 items-center rounded-xl bg-brand px-5 text-sm font-semibold text-white transition hover:bg-brand-hover">دیدن برنامه‌ها</a>
            </div>
        </div>
    </section>

    <section id="programs" class="bg-surface-page py-10">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid gap-8">
                @forelse ($categories as $category)
                    <section class="grid gap-4">
                        <div>
                            <p class="text-sm font-semibold text-brand">{{ $category->label }}</p>
                            <h2 class="mt-1 text-2xl font-semibold tracking-normal text-ink">{{ $category->title ?: $category->label }}</h2>
                        </div>

                        <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3 2xl:grid-cols-4">
                            @foreach ($category->programs as $program)
                                @php
                                    $imageUrl = \App\Support\Mobile\ImageUrl::resolve($program->image_url);
                                    $color = preg_match('/^#[0-9A-Fa-f]{3,8}$/', (string) $program->thumbnail_color) ? $program->thumbnail_color : '#101114';
                                    $remaining = $program->capacity === null ? null : max(0, $program->capacity - $program->reserved_count);
                                @endphp
                                <article class="group overflow-hidden rounded-2xl border border-border bg-surface shadow-sm transition hover:-translate-y-0.5 hover:border-brand hover:shadow-lg">
                                    <a href="{{ route('storefront.events.show', $program) }}" class="block">
                                        @if ($imageUrl)
                                            <img src="{{ $imageUrl }}" alt="{{ $program->title }}" class="aspect-[4/3] w-full object-cover" loading="lazy">
                                        @else
                                            <div class="grid aspect-[4/3] place-items-center text-white" style="background: radial-gradient(circle at 18% 18%, rgba(255,255,255,0.2), transparent 32%), linear-gradient(135deg, {{ $color }}, #1B4D3E 58%, #101114);" role="img" aria-label="{{ $program->title }}">
                                                <span class="text-3xl font-bold tracking-normal">{{ $program->thumbnail_text }}</span>
                                            </div>
                                        @endif
                                    </a>
                                    <div class="p-5">
                                        <div class="flex flex-wrap items-center gap-2 text-xs font-semibold">
                                            <span class="rounded-full bg-brand-soft px-2.5 py-1 text-brand">{{ $program->status_label }}</span>
                                            @if ($remaining !== null)
                                                <span class="rounded-full bg-surface-alt px-2.5 py-1 text-muted">{{ $remaining }} ظرفیت باقی‌مانده</span>
                                            @endif
                                        </div>
                                        <h3 class="mt-3 text-lg font-semibold text-ink">
                                            <a href="{{ route('storefront.events.show', $program) }}" class="transition hover:text-brand">{{ $program->title }}</a>
                                        </h3>
                                        <p class="mt-2 text-sm leading-6 text-muted">{{ $program->subtitle }}</p>
                                        <p class="mt-3 text-sm font-semibold text-ink">{{ $program->date_label }}</p>
                                        <a href="{{ route('storefront.events.show', $program) }}" class="mt-5 inline-flex min-h-11 w-full items-center justify-center rounded-xl border border-border bg-surface px-4 text-sm font-semibold text-ink transition hover:border-brand hover:text-brand">
                                            {{ $program->program_state === 'future' ? ($program->book_label ?: 'رزرو برنامه') : ($program->view_label ?: 'مشاهده برنامه') }}
                                        </a>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    </section>
                @empty
                    <div class="rounded-2xl border border-border bg-surface p-8 text-center">
                        <h2 class="text-xl font-semibold text-ink">برنامه‌ای ثبت نشده است</h2>
                        <p class="mt-2 text-muted">بعد از فعال شدن برنامه‌ها در پنل مدیریت، این صفحه به‌روزرسانی می‌شود.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </section>
@endsection
