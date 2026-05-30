<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ProductInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('category.label')
                    ->label('Category'),
                TextEntry::make('slug'),
                TextEntry::make('title'),
                TextEntry::make('subtitle'),
                TextEntry::make('description')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('availability'),
                TextEntry::make('price_value')
                    ->label('Price')
                    ->formatStateUsing(fn (?int $state): string => number_format($state ?? 0)),
                TextEntry::make('price_label')
                    ->placeholder('-'),
                TextEntry::make('stock_label')
                    ->placeholder('-'),
                TextEntry::make('thumbnail_text'),
                TextEntry::make('thumbnail_color'),
                TextEntry::make('image_url')
                    ->placeholder('-'),
                TextEntry::make('sort_order')
                    ->formatStateUsing(fn (?int $state): string => number_format($state ?? 0)),
                IconEntry::make('is_featured')
                    ->boolean(),
                IconEntry::make('is_active')
                    ->boolean(),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
