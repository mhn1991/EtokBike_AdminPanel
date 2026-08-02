<?php

namespace App\Filament\Resources\ProductBundles;

use App\Filament\Resources\ProductBundles\Pages\CreateProductBundle;
use App\Filament\Resources\ProductBundles\Pages\EditProductBundle;
use App\Filament\Resources\ProductBundles\Pages\ListProductBundles;
use App\Filament\Resources\ProductBundles\Schemas\ProductBundleForm;
use App\Models\ProductBundle;
use App\Support\Admin\FilamentLocalization;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ProductBundleResource extends Resource
{
    protected static ?string $model = ProductBundle::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedGift;

    protected static ?string $navigationLabel = 'بسته‌های کالا';

    protected static string|\UnitEnum|null $navigationGroup = 'بازاریابی';

    protected static ?int $navigationSort = 3;

    protected static ?string $modelLabel = 'بسته کالا';

    protected static ?string $pluralModelLabel = 'بسته‌های کالا';

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return ProductBundleForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->description(fn (ProductBundle $record): string => __(':count products', ['count' => $record->items()->count()]))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('type')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => __(ProductBundle::TYPE_OPTIONS[$state] ?? $state))
                    ->sortable(),
                TextColumn::make('bundle_price_value')
                    ->label('Price')
                    ->formatStateUsing(fn (?int $state): string => $state !== null ? number_format($state) : '-')
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
                TextColumn::make('ends_at')
                    ->dateTime()
                    ->placeholder(__('-'))
                    ->visibleFrom('lg')
                    ->sortable(),
                TextColumn::make('sort_order')
                    ->visibleFrom('lg')
                    ->sortable(),
            ])
            ->emptyStateIcon(Heroicon::OutlinedGift)
            ->emptyStateHeading(__('No bundles yet'))
            ->emptyStateDescription(__('Combine existing products into a fixed-price bundle, or link them as frequently bought together.'))
            ->filters([
                SelectFilter::make('type')->options(FilamentLocalization::options(ProductBundle::TYPE_OPTIONS)),
            ])
            ->defaultSort('sort_order')
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProductBundles::route('/'),
            'create' => CreateProductBundle::route('/create'),
            'edit' => EditProductBundle::route('/{record}/edit'),
        ];
    }
}
