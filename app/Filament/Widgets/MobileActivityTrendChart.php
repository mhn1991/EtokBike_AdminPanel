<?php

namespace App\Filament\Widgets;

use App\Models\MobileAnalyticsEvent;
use App\Support\Admin\DashboardMetrics;
use Filament\Widgets\ChartWidget;
use Illuminate\Database\Eloquent\Builder;

class MobileActivityTrendChart extends ChartWidget
{
    protected static bool $isLazy = false;

    protected static ?int $sort = 7;

    protected int|string|array $columnSpan = 'full';

    protected ?string $heading = 'روند استفاده از موبایل';

    protected ?string $description = 'دستگاه‌های فعال روزانه و مجموع رویدادهای موبایل در دو هفته گذشته.';

    protected ?string $maxHeight = '240px';

    protected ?string $pollingInterval = null;

    protected function getType(): string
    {
        return 'line';
    }

    public static function canView(): bool
    {
        return MobileAnalyticsEvent::query()->exists();
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
                    'label' => __('Active devices'),
                    'data' => DashboardMetrics::distinctCountByDay(MobileAnalyticsEvent::class, 'device_id', $days, column: 'occurred_at'),
                    'borderColor' => '#16a34a',
                    'backgroundColor' => 'rgba(22, 163, 74, 0.12)',
                    'tension' => 0.35,
                ],
                [
                    'label' => __('Events'),
                    'data' => DashboardMetrics::countByDay(MobileAnalyticsEvent::class, $days, column: 'occurred_at'),
                    'borderColor' => '#2563eb',
                    'backgroundColor' => 'rgba(37, 99, 235, 0.12)',
                    'tension' => 0.35,
                ],
                [
                    'label' => __('Errors'),
                    'data' => DashboardMetrics::countByDay(
                        MobileAnalyticsEvent::class,
                        $days,
                        fn (Builder $query) => $query->where('event_name', 'error'),
                        'occurred_at',
                    ),
                    'borderColor' => '#dc2626',
                    'backgroundColor' => 'rgba(220, 38, 38, 0.10)',
                    'tension' => 0.35,
                ],
            ],
            'labels' => DashboardMetrics::labelsForLastDays($days),
        ];
    }
}
