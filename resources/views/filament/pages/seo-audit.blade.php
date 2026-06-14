<x-filament-panels::page>
    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        @foreach ($this->metrics() as $label => $value)
            <section class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ $label }}</p>
                <p class="mt-2 text-2xl font-semibold text-gray-950 dark:text-white">{{ number_format($value) }}</p>
            </section>
        @endforeach
    </div>
</x-filament-panels::page>
