@php
    $imageUrl = \App\Support\Mobile\ImageUrl::resolve($product->image_url);
    $color = preg_match('/^#[0-9A-Fa-f]{3,8}$/', (string) $product->thumbnail_color) ? $product->thumbnail_color : '#101114';
    $classes = $class ?? 'aspect-[4/3]';
@endphp

@if ($imageUrl)
    <img
        src="{{ $imageUrl }}"
        alt="{{ $product->title }}"
        class="{{ $classes }} w-full rounded-md object-cover"
        loading="{{ $loading ?? 'lazy' }}"
    >
@else
    <div
        class="{{ $classes }} grid w-full place-items-center rounded-md text-white"
        style="background: linear-gradient(135deg, {{ $color }}, #171717);"
        role="img"
        aria-label="{{ $product->title }}"
    >
        <span class="text-3xl font-bold tracking-normal sm:text-4xl">{{ $product->thumbnail_text }}</span>
    </div>
@endif
