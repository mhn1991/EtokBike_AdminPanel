<?php

namespace App\Filament\Resources\ProductOffers;

use App\Filament\Resources\ProductOffers\Pages\CreateProductOffer;
use App\Filament\Resources\ProductOffers\Pages\EditProductOffer;
use App\Filament\Resources\ProductOffers\Pages\ListProductOffers;
use App\Filament\Resources\ProductOffers\Schemas\ProductOfferForm;
use App\Filament\Resources\ProductOffers\Tables\ProductOffersTable;
use App\Models\ProductOffer;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ProductOfferResource extends Resource
{
    protected static ?string $model = ProductOffer::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTag;

    protected static ?string $navigationLabel = 'پیشنهادهای کالا';

    protected static string|\UnitEnum|null $navigationGroup = 'بازاریابی';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'پیشنهاد کالا';

    protected static ?string $pluralModelLabel = 'پیشنهادهای کالا';

    public static function form(Schema $schema): Schema
    {
        return ProductOfferForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProductOffersTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProductOffers::route('/'),
            'create' => CreateProductOffer::route('/create'),
            'edit' => EditProductOffer::route('/{record}/edit'),
        ];
    }
}
