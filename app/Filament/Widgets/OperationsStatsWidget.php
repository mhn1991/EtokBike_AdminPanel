<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\CustomerMessages\CustomerMessageResource;
use App\Filament\Resources\Orders\OrderResource;
use App\Filament\Resources\Products\ProductResource;
use App\Filament\Resources\ServiceBookings\ServiceBookingResource;
use App\Models\CustomerMessage;
use App\Models\Order;
use App\Models\Product;
use App\Models\ServiceBooking;
use App\Support\Admin\DashboardMetrics;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Builder;

class OperationsStatsWidget extends StatsOverviewWidget
{
    protected static bool $isLazy = false;

    protected static ?int $sort = 0;

    protected ?string $heading = 'نمای عملیات';

    protected ?string $description = 'وضعیت فعلی سفارش‌ها، خدمات، پیام‌ها و سلامت کاتالوگ.';

    protected ?string $pollingInterval = null;

    /**
     * @return array<Stat>
     */
    protected function getStats(): array
    {
        $openOrderStatuses = ['pending', 'confirmed', 'processing', 'ready'];
        $openBookingStatuses = ['pending', 'confirmed', 'in_progress'];

        $openOrders = Order::query()
            ->whereIn('status', $openOrderStatuses)
            ->count();

        $todayRevenue = Order::query()
            ->whereDate('placed_at', today())
            ->where('status', '!=', 'cancelled')
            ->sum('total');

        $openBookings = ServiceBooking::query()
            ->whereIn('status', $openBookingStatuses)
            ->count();

        $unreadMessages = CustomerMessage::query()
            ->where('is_unread', true)
            ->count();

        $lowStockProducts = Product::query()
            ->where('is_active', true)
            ->whereIn('availability', ['low_stock', 'out_of_stock'])
            ->count();

        return [
            Stat::make(__('Open orders'), DashboardMetrics::number($openOrders))
                ->description(__('Placed today: :amount', ['amount' => DashboardMetrics::money($todayRevenue)]))
                ->descriptionIcon(Heroicon::ArrowTrendingUp)
                ->chart(DashboardMetrics::countByDay(Order::class, 7))
                ->color('warning')
                ->icon(Heroicon::ShoppingCart)
                ->url(OrderResource::getUrl()),
            Stat::make(__('Service queue'), DashboardMetrics::number($openBookings))
                ->description(__('Bookings still needing workshop action'))
                ->chart(DashboardMetrics::countByDay(
                    ServiceBooking::class,
                    7,
                    fn (Builder $query) => $query->whereIn('status', $openBookingStatuses),
                ))
                ->color('info')
                ->icon(Heroicon::WrenchScrewdriver)
                ->url(ServiceBookingResource::getUrl()),
            Stat::make(__('Unread messages'), DashboardMetrics::number($unreadMessages))
                ->description(__('Customer conversations waiting for a reply'))
                ->chart(DashboardMetrics::countByDay(
                    CustomerMessage::class,
                    7,
                    fn (Builder $query) => $query->where('is_unread', true),
                ))
                ->color($unreadMessages > 0 ? 'danger' : 'success')
                ->icon(Heroicon::EnvelopeOpen)
                ->url(CustomerMessageResource::getUrl()),
            Stat::make(__('Low-stock products'), DashboardMetrics::number($lowStockProducts))
                ->description(__('Items that need stock or visibility review'))
                ->chart(DashboardMetrics::countByDay(
                    Product::class,
                    7,
                    fn (Builder $query) => $query
                        ->where('is_active', true)
                        ->whereIn('availability', ['low_stock', 'out_of_stock']),
                ))
                ->color($lowStockProducts > 0 ? 'warning' : 'success')
                ->icon(Heroicon::ShoppingBag)
                ->url(ProductResource::getUrl()),
        ];
    }
}
