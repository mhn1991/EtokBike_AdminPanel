@extends('storefront.layouts.app')

@section('content')
    <article class="mx-auto w-full max-w-5xl px-4 py-10 sm:px-6 lg:px-8">
        <nav class="mb-6 text-sm text-muted" aria-label="Breadcrumb">
            <a class="transition hover:text-brand" href="{{ route('storefront.home') }}">خانه</a>
            <span aria-hidden="true"> / </span>
            <span>{{ $page->title }}</span>
        </nav>

        <header class="border-b border-border pb-6">
            <h1 class="text-3xl font-semibold tracking-normal text-ink">{{ $page->title }}</h1>
            @if ($page->excerpt)
                <p class="mt-4 text-base leading-8 text-muted">{{ $page->excerpt }}</p>
            @endif
        </header>

        <div class="prose prose-neutral mt-8 max-w-none leading-8 text-ink">
            {{ $page->richBody() }}
        </div>
    </article>
@endsection
