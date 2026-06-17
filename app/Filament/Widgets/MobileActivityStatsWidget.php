<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\MobileAnalyticsEvents\MobileAnalyticsEventResource;
use App\Models\MobileAnalyticsEvent;
use App\Support\Admin\DashboardMetrics;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Builder;

class MobileActivityStatsWidget extends StatsOverviewWidget
{
    protected static bool $isLazy = false;

    protected static ?int $sort = 4;

    protected ?string $heading = 'فعالیت اپ موبایل';

    protected ?string $description = 'مصرف زنده اپ بر اساس رویدادهای تله‌متری موبایل.';

    protected ?string $pollingInterval = null;

    public static function canView(): bool
    {
        return MobileAnalyticsEvent::query()->exists();
    }

    /**
     * @return array<Stat>
     */
    protected function getStats(): array
    {
        $activeUsers = MobileAnalyticsEvent::query()
            ->where('occurred_at', '>=', now()->subMinutes(15))
            ->distinct('device_id')
            ->count('device_id');

        $dailyActiveUsers = MobileAnalyticsEvent::query()
            ->where('occurred_at', '>=', today())
            ->distinct('device_id')
            ->count('device_id');

        $eventsToday = MobileAnalyticsEvent::query()
            ->where('occurred_at', '>=', today())
            ->count();

        $errorsToday = MobileAnalyticsEvent::query()
            ->where('event_name', 'error')
            ->where('occurred_at', '>=', today())
            ->count();

        return [
            Stat::make(__('Active users'), DashboardMetrics::number($activeUsers))
                ->description(__('Unique devices seen in the last 15 minutes'))
                ->chart(DashboardMetrics::distinctCountByDay(MobileAnalyticsEvent::class, 'device_id', 7))
                ->color('success')
                ->icon(Heroicon::DevicePhoneMobile)
                ->url(MobileAnalyticsEventResource::getUrl()),
            Stat::make(__('Daily active users'), DashboardMetrics::number($dailyActiveUsers))
                ->description(__('Unique devices seen since midnight'))
                ->chart(DashboardMetrics::distinctCountByDay(MobileAnalyticsEvent::class, 'device_id', 7))
                ->color('info')
                ->icon(Heroicon::Users)
                ->url(MobileAnalyticsEventResource::getUrl()),
            Stat::make(__('Events today'), DashboardMetrics::number($eventsToday))
                ->description(__('Screen views, taps, opens, and heartbeats'))
                ->chart(DashboardMetrics::countByDay(MobileAnalyticsEvent::class, 7, column: 'occurred_at'))
                ->color('primary')
                ->icon(Heroicon::CursorArrowRays)
                ->url(MobileAnalyticsEventResource::getUrl()),
            Stat::make(__('Phone errors'), DashboardMetrics::number($errorsToday))
                ->description(__('Error events reported by the app today'))
                ->chart(DashboardMetrics::countByDay(
                    MobileAnalyticsEvent::class,
                    7,
                    fn (Builder $query) => $query->where('event_name', 'error'),
                    'occurred_at',
                ))
                ->color($errorsToday > 0 ? 'danger' : 'success')
                ->icon(Heroicon::ExclamationTriangle)
                ->url(MobileAnalyticsEventResource::getUrl()),
        ];
    }
}
