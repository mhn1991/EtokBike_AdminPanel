<?php

namespace App\Filament\Resources\ServiceBookings;

use App\Filament\Resources\ServiceBookings\Pages\CreateServiceBooking;
use App\Filament\Resources\ServiceBookings\Pages\EditServiceBooking;
use App\Filament\Resources\ServiceBookings\Pages\ListServiceBookings;
use App\Filament\Resources\ServiceBookings\Pages\ViewServiceBooking;
use App\Filament\Resources\ServiceBookings\Schemas\ServiceBookingForm;
use App\Filament\Resources\ServiceBookings\Schemas\ServiceBookingInfolist;
use App\Filament\Resources\ServiceBookings\Tables\ServiceBookingsTable;
use App\Models\ServiceBooking;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ServiceBookingResource extends Resource
{
    protected static ?string $model = ServiceBooking::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = 'Service bookings';

    protected static string|\UnitEnum|null $navigationGroup = 'Services';

    protected static ?string $modelLabel = 'service booking';

    protected static ?string $pluralModelLabel = 'service bookings';

    protected static ?string $recordTitleAttribute = 'customer_name';

    public static function form(Schema $schema): Schema
    {
        return ServiceBookingForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ServiceBookingInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ServiceBookingsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListServiceBookings::route('/'),
            'create' => CreateServiceBooking::route('/create'),
            'view' => ViewServiceBooking::route('/{record}'),
            'edit' => EditServiceBooking::route('/{record}/edit'),
        ];
    }
}
