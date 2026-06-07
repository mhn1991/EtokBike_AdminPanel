<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Models\Product;
use Filament\Infolists\Components\ColorEntry;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
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
                            ->label('Category'),
                        TextEntry::make('slug'),
                        TextEntry::make('availability')
                            ->badge()
                            ->formatStateUsing(fn (string $state): string => Product::AVAILABILITY_OPTIONS[$state] ?? $state)
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
                            ->placeholder('-')
                            ->columnSpanFull(),
                    ]),
                Section::make('Pricing and stock')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('price_value')
                            ->label('Price')
                            ->formatStateUsing(fn (?int $state): string => number_format($state ?? 0)),
                        TextEntry::make('price_label')
                            ->placeholder('-'),
                        TextEntry::make('stock_label')
                            ->placeholder('-'),
                        IconEntry::make('is_featured')
                            ->boolean(),
                        IconEntry::make('is_active')
                            ->label('Visible in app')
                            ->boolean(),
                    ]),
                Section::make('App card')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('thumbnail_text'),
                        ColorEntry::make('thumbnail_color'),
                        ImageEntry::make('image_url')
                            ->disk('public')
                            ->placeholder('-'),
                    ]),
                Section::make('Audit')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('created_at')
                            ->dateTime()
                            ->placeholder('-'),
                        TextEntry::make('updated_at')
                            ->dateTime()
                            ->placeholder('-'),
                    ]),
            ]);
    }
}
