<?php

namespace App\Filament\Resources\ProductOffers\Schemas;

use App\Models\ProductOffer;
use App\Support\Admin\FilamentLocalization;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class ProductOfferForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Select::make('product_id')
                    ->label('Product')
                    ->relationship('product', 'title')
                    ->native(false)
                    ->searchable()
                    ->preload()
                    ->required()
                    ->columnSpanFull(),
                ...self::fields(),
            ]);
    }

    /**
     * Shared offer field set, reused by the standalone resource form above
     * and by the "Offers" repeater embedded in the product create/edit form
     * (App\Filament\Resources\Products\Schemas\ProductForm). Deliberately
     * excludes the `product_id` select — the repeater is already scoped to
     * one product via its relationship.
     *
     * @return array<int, \Filament\Schemas\Components\Component>
     */
    public static function fields(): array
    {
        return [
            ToggleButtons::make('type')
                ->options(FilamentLocalization::options(ProductOffer::TYPE_OPTIONS))
                ->inline()
                ->live()
                ->required()
                ->default('sale_price')
                ->columnSpanFull(),
            TextInput::make('title')
                ->helperText(__('Internal label only, shown to staff in this list — not shown to customers.'))
                ->placeholder(__('Example: Summer sale'))
                ->required()
                ->maxLength(255),
            TextInput::make('badge_text')
                ->helperText(__('Optional customer-facing badge text, e.g. "20% OFF" or "2-for-1".'))
                ->maxLength(255),
            TextInput::make('sale_price_value')
                ->label('Sale price')
                ->integer()
                ->minValue(0)
                ->suffix(__('Toman'))
                ->helperText(__('The discounted price shown instead of the base price while this offer is active.'))
                ->visible(fn (Get $get): bool => $get('type') === 'sale_price')
                ->required(fn (Get $get): bool => $get('type') === 'sale_price'),
            TextInput::make('buy_quantity')
                ->label('Buy quantity')
                ->integer()
                ->minValue(1)
                ->helperText(__('Units the customer must buy to trigger the deal.'))
                ->visible(fn (Get $get): bool => $get('type') === 'buy_x_get_y')
                ->required(fn (Get $get): bool => $get('type') === 'buy_x_get_y'),
            TextInput::make('get_quantity')
                ->label('Get quantity')
                ->integer()
                ->minValue(1)
                ->helperText(__('Extra units the customer receives at a discount.'))
                ->visible(fn (Get $get): bool => $get('type') === 'buy_x_get_y')
                ->required(fn (Get $get): bool => $get('type') === 'buy_x_get_y'),
            TextInput::make('get_discount_percent')
                ->label('Discount on those units')
                ->integer()
                ->minValue(1)
                ->maxValue(100)
                ->suffix('%')
                ->default(100)
                ->helperText(__('100% means the extra units are free.'))
                ->visible(fn (Get $get): bool => $get('type') === 'buy_x_get_y')
                ->required(fn (Get $get): bool => $get('type') === 'buy_x_get_y'),
            Select::make('discount_type')
                ->options(FilamentLocalization::options(ProductOffer::DISCOUNT_TYPE_OPTIONS))
                ->native(false)
                ->visible(fn (Get $get): bool => $get('type') === 'auto_discount')
                ->required(fn (Get $get): bool => $get('type') === 'auto_discount'),
            TextInput::make('discount_value')
                ->integer()
                ->minValue(0)
                ->helperText(__('Percentage or fixed toman amount, depending on the discount type above.'))
                ->visible(fn (Get $get): bool => $get('type') === 'auto_discount')
                ->required(fn (Get $get): bool => $get('type') === 'auto_discount'),
            TextInput::make('minimum_quantity')
                ->integer()
                ->minValue(1)
                ->default(1)
                ->helperText(__('Minimum quantity in the cart for this discount to apply automatically.'))
                ->visible(fn (Get $get): bool => $get('type') === 'auto_discount'),
            DateTimePicker::make('starts_at')
                ->seconds(false)
                ->helperText(__('Leave blank to start immediately.')),
            DateTimePicker::make('ends_at')
                ->seconds(false)
                ->helperText(__('Leave blank to run with no end date.')),
            Toggle::make('is_active')
                ->required()
                ->default(true),
        ];
    }
}
