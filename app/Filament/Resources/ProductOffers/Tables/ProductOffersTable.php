<?php

namespace App\Filament\Resources\ProductOffers\Tables;

use App\Models\ProductOffer;
use App\Support\Admin\FilamentLocalization;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ProductOffersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('product.title')
                    ->label('Product')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('type')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => __(ProductOffer::TYPE_OPTIONS[$state] ?? $state))
                    ->sortable(),
                TextColumn::make('title')
                    ->label('Label')
                    ->searchable(),
                TextColumn::make('summary')
                    ->label('Deal')
                    ->state(fn (ProductOffer $record): string => self::summarize($record)),
                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
                TextColumn::make('ends_at')
                    ->dateTime()
                    ->placeholder(__('-'))
                    ->visibleFrom('lg')
                    ->sortable(),
            ])
            ->emptyStateIcon(Heroicon::OutlinedTag)
            ->emptyStateHeading(__('No offers yet'))
            ->emptyStateDescription(__('Create a sale price, buy-X-get-Y deal, or automatic discount for a product.'))
            ->filters([
                SelectFilter::make('type')->options(FilamentLocalization::options(ProductOffer::TYPE_OPTIONS)),
                SelectFilter::make('product')->relationship('product', 'title')->searchable()->preload(),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    private static function summarize(ProductOffer $record): string
    {
        return match ($record->type) {
            'sale_price' => __('Sale price: :price', ['price' => number_format($record->sale_price_value ?? 0)]),
            'buy_x_get_y' => __('Buy :buy get :get at :discount% off', [
                'buy' => $record->buy_quantity ?? 0,
                'get' => $record->get_quantity ?? 0,
                'discount' => $record->get_discount_percent ?? 0,
            ]),
            'auto_discount' => __(':value:type off orders of :min+', [
                'value' => $record->discount_value ?? 0,
                'type' => $record->discount_type === 'percent' ? '%' : ' toman',
                'min' => $record->minimum_quantity ?? 1,
            ]),
            default => $record->type,
        };
    }
}
