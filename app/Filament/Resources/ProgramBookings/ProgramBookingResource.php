<?php

namespace App\Filament\Resources\ProgramBookings;

use App\Filament\Resources\ProgramBookings\Pages\CreateProgramBooking;
use App\Filament\Resources\ProgramBookings\Pages\EditProgramBooking;
use App\Filament\Resources\ProgramBookings\Pages\ListProgramBookings;
use App\Filament\Resources\ProgramBookings\Pages\ViewProgramBooking;
use App\Filament\Resources\ProgramBookings\Schemas\ProgramBookingForm;
use App\Filament\Resources\ProgramBookings\Schemas\ProgramBookingInfolist;
use App\Filament\Resources\ProgramBookings\Tables\ProgramBookingsTable;
use App\Models\ProgramBooking;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ProgramBookingResource extends Resource
{
    protected static ?string $model = ProgramBooking::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTicket;

    protected static ?string $navigationLabel = 'Program bookings';

    protected static string|\UnitEnum|null $navigationGroup = 'Programs';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'program booking';

    protected static ?string $pluralModelLabel = 'program bookings';

    protected static ?string $recordTitleAttribute = 'customer_name';

    public static function getNavigationBadge(): ?string
    {
        return number_format(ProgramBooking::query()->whereIn('status', ['pending', 'confirmed'])->count());
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'info';
    }

    public static function form(Schema $schema): Schema
    {
        return ProgramBookingForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ProgramBookingInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProgramBookingsTable::configure($table);
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
            'index' => ListProgramBookings::route('/'),
            'create' => CreateProgramBooking::route('/create'),
            'view' => ViewProgramBooking::route('/{record}'),
            'edit' => EditProgramBooking::route('/{record}/edit'),
        ];
    }
}
