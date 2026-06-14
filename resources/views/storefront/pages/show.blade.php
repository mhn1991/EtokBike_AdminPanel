@extends('storefront.layouts.app')

@section('content')
    <article class="mx-auto max-w-3xl px-4 py-10 sm:px-6 lg:px-8">
        <nav class="mb-6 text-sm text-neutral-600" aria-label="Breadcrumb">
            <a class="hover:text-red-700" href="{{ route('storefront.home') }}">خانه</a>
            <span aria-hidden="true"> / </span>
            <span>{{ $page->title }}</span>
        </nav>

        <header class="border-b border-neutral-200 pb-6">
            <h1 class="text-3xl font-semibold tracking-normal text-neutral-950">{{ $page->title }}</h1>
            @if ($page->excerpt)
                <p class="mt-4 text-base leading-8 text-neutral-700">{{ $page->excerpt }}</p>
            @endif
        </header>

        <div class="prose prose-neutral mt-8 max-w-none leading-8 text-neutral-800">
            {!! nl2br(e($page->body ?? '')) !!}
        </div>
    </article>
@endsection
