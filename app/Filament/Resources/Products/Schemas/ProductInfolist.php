<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Models\Product;
use Filament\Infolists\Components\ColorEntry;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\RepeatableEntry\TableColumn;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProductInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Product')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('category.label')
                            ->label('Category')
                            ->formatStateUsing(fn (?string $state, Product $record): string => $record->category?->pathLabel() ?? (string) $state),
                        TextEntry::make('slug'),
                        TextEntry::make('sku')
                            ->label('SKU')
                            ->placeholder(__('-')),
                        TextEntry::make('availability')
                            ->badge()
                            ->formatStateUsing(fn (string $state): string => __(Product::AVAILABILITY_OPTIONS[$state] ?? $state))
                            ->color(fn (string $state): string => match ($state) {
                                'in_stock' => 'success',
                                'low_stock' => 'warning',
                                'orderable' => 'info',
                                'out_of_stock' => 'danger',
                                default => 'gray',
                            }),
                        TextEntry::make('title'),
                        TextEntry::make('subtitle'),
                        TextEntry::make('sort_order')
                            ->formatStateUsing(fn (?int $state): string => number_format($state ?? 0)),
                        TextEntry::make('description')
                            ->placeholder(__('-'))
                            ->columnSpanFull(),
                    ]),
                Section::make('Pricing and stock')
                    ->columns(4)
                    ->schema([
                        TextEntry::make('price_value')
                            ->label('Price')
                            ->formatStateUsing(fn (?int $state): string => number_format($state ?? 0)),
                        TextEntry::make('price_label')
                            ->placeholder(__('-')),
                        TextEntry::make('stock_label')
                            ->placeholder(__('-')),
                        TextEntry::make('stock_quantity')
                            ->label('On hand')
                            ->formatStateUsing(fn (?int $state): string => number_format($state ?? 0)),
                        TextEntry::make('reserved_quantity')
                            ->label('Reserved')
                            ->formatStateUsing(fn (?int $state): string => number_format($state ?? 0)),
                        TextEntry::make('minimum_stock')
                            ->label('Low stock alert')
                            ->formatStateUsing(fn (?int $state): string => number_format($state ?? 0)),
                        TextEntry::make('warehouse_location')
                            ->label('Location')
                            ->placeholder(__('-')),
                        IconEntry::make('is_featured')
                            ->boolean(),
                        IconEntry::make('is_active')
                            ->label('Visible in app')
                            ->boolean(),
                    ]),
                Section::make('Variants')
                    ->description(__('Color, size, stock amount, and price options for this product.'))
                    ->schema([
                        RepeatableEntry::make('variants')
                            ->label('Product variants')
                            ->table([
                                TableColumn::make('Name'),
                                TableColumn::make('Color'),
                                TableColumn::make('Size'),
                                TableColumn::make('Price'),
                                TableColumn::make('Amount'),
                                TableColumn::make('Active'),
                            ])
                            ->schema([
                                TextEntry::make('name'),
                                TextEntry::make('options.color')
                                    ->label('Color')
                                    ->placeholder(__('-')),
                                TextEntry::make('options.size')
                                    ->label('Size')
                                    ->placeholder(__('-')),
                                TextEntry::make('price_value')
                                    ->label('Price')
                                    ->formatStateUsing(fn (?int $state): string => filled($state) ? number_format($state) : __('Base price')),
                                TextEntry::make('stock_quantity')
                                    ->label('Amount')
                                    ->formatStateUsing(fn (?int $state): string => number_format($state ?? 0)),
                                IconEntry::make('is_active')
                                    ->label('Active')
                                    ->boolean(),
                            ]),
                    ]),
                Section::make('App card')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('thumbnail_text'),
                        ColorEntry::make('thumbnail_color'),
                        ImageEntry::make('image_url')
                            ->disk('public')
                            ->placeholder(__('-')),
                    ]),
                Section::make('Audit')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('created_at')
                            ->dateTime()
                            ->placeholder(__('-')),
                        TextEntry::make('updated_at')
                            ->dateTime()
                            ->placeholder(__('-')),
                    ]),
            ]);
    }
}
