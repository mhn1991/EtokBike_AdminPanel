@php
    $imageUrl = \App\Support\Mobile\ImageUrl::resolve($product->image_url);
    $color = preg_match('/^#[0-9A-Fa-f]{3,8}$/', (string) $product->thumbnail_color) ? $product->thumbnail_color : '#101114';
    $classes = trim(($class ?? 'aspect-[4/3]').' '.($radius ?? 'rounded-xl'));
@endphp

@if ($imageUrl)
    <img
        src="{{ $imageUrl }}"
        alt="{{ $product->title }}"
        class="{{ $classes }} w-full object-cover"
        loading="{{ $loading ?? 'lazy' }}"
    >
@else
    <div
        class="{{ $classes }} grid w-full place-items-center overflow-hidden text-white"
        style="background: radial-gradient(circle at 18% 18%, rgba(255,255,255,0.2), transparent 32%), linear-gradient(135deg, {{ $color }}, #1B4D3E 58%, #101114);"
        role="img"
        aria-label="{{ $product->title }}"
    >
        <span class="px-5 text-center text-3xl font-bold tracking-normal sm:text-4xl">{{ $product->thumbnail_text }}</span>
    </div>
@endif
