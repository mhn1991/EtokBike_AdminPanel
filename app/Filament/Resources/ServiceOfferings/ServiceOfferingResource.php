<?php

namespace App\Filament\Resources\ServiceOfferings;

use App\Filament\Resources\ServiceOfferings\Pages\CreateServiceOffering;
use App\Filament\Resources\ServiceOfferings\Pages\EditServiceOffering;
use App\Filament\Resources\ServiceOfferings\Pages\ListServiceOfferings;
use App\Filament\Resources\ServiceOfferings\Pages\ViewServiceOffering;
use App\Filament\Resources\ServiceOfferings\Schemas\ServiceOfferingForm;
use App\Filament\Resources\ServiceOfferings\Schemas\ServiceOfferingInfolist;
use App\Filament\Resources\ServiceOfferings\Tables\ServiceOfferingsTable;
use App\Models\ServiceOffering;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ServiceOfferingResource extends Resource
{
    protected static ?string $model = ServiceOffering::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = 'Service offerings';

    protected static string|\UnitEnum|null $navigationGroup = 'Services';

    protected static ?string $modelLabel = 'service offering';

    protected static ?string $pluralModelLabel = 'service offerings';

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return ServiceOfferingForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ServiceOfferingInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ServiceOfferingsTable::configure($table);
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
            'index' => ListServiceOfferings::route('/'),
            'create' => CreateServiceOffering::route('/create'),
            'view' => ViewServiceOffering::route('/{record}'),
            'edit' => EditServiceOffering::route('/{record}/edit'),
        ];
    }
}
