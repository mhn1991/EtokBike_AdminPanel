<nav aria-label="Breadcrumb" class="text-sm text-neutral-600">
    <ol class="flex flex-wrap items-center gap-2">
        @foreach ($items as $item)
            <li class="flex items-center gap-2">
                @if (! $loop->first)
                    <span aria-hidden="true">/</span>
                @endif
                @if (! $loop->last)
                    <a href="{{ $item['url'] }}" class="hover:text-red-700">{{ $item['name'] }}</a>
                @else
                    <span class="font-medium text-neutral-950" aria-current="page">{{ $item['name'] }}</span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
