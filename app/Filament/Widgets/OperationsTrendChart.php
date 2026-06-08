<?php

namespace App\Filament\Widgets;

use App\Models\CustomerMessage;
use App\Models\Order;
use App\Models\ServiceBooking;
use App\Support\Admin\DashboardMetrics;
use Filament\Widgets\ChartWidget;

class OperationsTrendChart extends ChartWidget
{
    protected static bool $isLazy = false;

    protected static ?int $sort = 5;

    protected int|string|array $columnSpan = [
        'md' => 2,
        'xl' => 2,
    ];

    protected ?string $heading = 'Daily operations';

    protected ?string $description = 'Orders, service bookings, and customer messages created over the last two weeks.';

    protected ?string $maxHeight = '240px';

    protected ?string $pollingInterval = null;

    protected function getType(): string
    {
        return 'line';
    }

    public static function canView(): bool
    {
        return Order::query()->exists()
            || ServiceBooking::query()->exists()
            || CustomerMessage::query()->exists();
    }

    /**
     * @return array<string, mixed>
     */
    protected function getData(): array
    {
        $days = 14;

        return [
            'datasets' => [
                [
                    'label' => 'Orders',
                    'data' => DashboardMetrics::countByDay(Order::class, $days),
                    'borderColor' => '#f59e0b',
                    'backgroundColor' => 'rgba(245, 158, 11, 0.15)',
                    'tension' => 0.35,
                ],
                [
                    'label' => 'Service bookings',
                    'data' => DashboardMetrics::countByDay(ServiceBooking::class, $days),
                    'borderColor' => '#2563eb',
                    'backgroundColor' => 'rgba(37, 99, 235, 0.12)',
                    'tension' => 0.35,
                ],
                [
                    'label' => 'Messages',
                    'data' => DashboardMetrics::countByDay(CustomerMessage::class, $days),
                    'borderColor' => '#dc2626',
                    'backgroundColor' => 'rgba(220, 38, 38, 0.10)',
                    'tension' => 0.35,
                ],
            ],
            'labels' => DashboardMetrics::labelsForLastDays($days),
        ];
    }
}
