<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\ChartWidget;

class OrdersByStatusChart extends ChartWidget
{
    protected static bool $isLazy = false;

    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = [
        'md' => 1,
        'xl' => 1,
    ];

    protected ?string $heading = 'Order status mix';

    protected ?string $description = 'Live distribution of the order queue.';

    protected ?string $maxHeight = '320px';

    protected ?string $pollingInterval = null;

    protected function getType(): string
    {
        return 'doughnut';
    }

    /**
     * @return array<string, mixed>
     */
    protected function getData(): array
    {
        $statuses = array_keys(Order::STATUS_OPTIONS);
        $counts = Order::query()
            ->selectRaw('status, count(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        return [
            'datasets' => [
                [
                    'data' => collect($statuses)
                        ->map(fn (string $status): int => (int) ($counts[$status] ?? 0))
                        ->all(),
                    'backgroundColor' => [
                        '#f59e0b',
                        '#2563eb',
                        '#0ea5e9',
                        '#7c3aed',
                        '#16a34a',
                        '#dc2626',
                    ],
                    'borderWidth' => 0,
                ],
            ],
            'labels' => collect($statuses)
                ->map(fn (string $status): string => Order::STATUS_OPTIONS[$status])
                ->all(),
        ];
    }
}
