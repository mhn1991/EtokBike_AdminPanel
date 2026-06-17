<?php

namespace App\Filament\Resources\MobileAnalyticsEvents;

use App\Filament\Resources\MobileAnalyticsEvents\Pages\ListMobileAnalyticsEvents;
use App\Filament\Resources\MobileAnalyticsEvents\Pages\ViewMobileAnalyticsEvent;
use App\Filament\Resources\MobileAnalyticsEvents\Schemas\MobileAnalyticsEventInfolist;
use App\Filament\Resources\MobileAnalyticsEvents\Tables\MobileAnalyticsEventsTable;
use App\Models\MobileAnalyticsEvent;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class MobileAnalyticsEventResource extends Resource
{
    protected static ?string $model = MobileAnalyticsEvent::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;

    protected static ?string $navigationLabel = 'لاگ‌های تحلیل اپ';

    protected static string|\UnitEnum|null $navigationGroup = 'محتوای اپ موبایل';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'لاگ تحلیل اپ';

    protected static ?string $pluralModelLabel = 'لاگ‌های تحلیل اپ';

    protected static ?string $recordTitleAttribute = 'event_name';

    public static function getNavigationBadge(): ?string
    {
        $errorsToday = MobileAnalyticsEvent::query()
            ->where('event_name', 'error')
            ->where('occurred_at', '>=', today())
            ->count();

        return $errorsToday > 0 ? number_format($errorsToday) : null;
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'danger';
    }

    public static function infolist(Schema $schema): Schema
    {
        return MobileAnalyticsEventInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MobileAnalyticsEventsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMobileAnalyticsEvents::route('/'),
            'view' => ViewMobileAnalyticsEvent::route('/{record}'),
        ];
    }
}
