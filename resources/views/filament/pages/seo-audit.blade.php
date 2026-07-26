<x-filament-panels::page>
    <div style="display: grid; gap: 1rem; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));">
        @foreach ($this->metrics() as $label => $metric)
            @php
                $colors = match ($metric['severity']) {
                    'critical' => ['border' => '#fca5a5', 'value' => '#dc2626'],
                    'warning' => ['border' => '#fcd34d', 'value' => '#d97706'],
                    'info' => ['border' => '#e5e7eb', 'value' => '#0a0a0a'],
                    default => ['border' => '#6ee7b7', 'value' => '#059669'],
                };
            @endphp
            <section style="border: 1px solid {{ $colors['border'] }}; border-radius: 0.5rem; padding: 1rem; background: var(--fi-panel-bg, #fff);">
                <p style="font-size: 0.875rem; font-weight: 500; color: #6b7280;">{{ $label }}</p>
                <p style="margin-top: 0.5rem; font-size: 1.5rem; font-weight: 600; color: {{ $colors['value'] }};">{{ number_format($metric['value']) }}</p>
                <p style="margin-top: 0.5rem; font-size: 0.75rem; color: #6b7280;">{{ $metric['hint'] }}</p>
            </section>
        @endforeach
    </div>
</x-filament-panels::page>
