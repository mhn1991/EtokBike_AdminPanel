<?php

namespace App\Filament\Resources\ServiceOfferings\Schemas;

use Filament\Infolists\Components\ColorEntry;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ServiceOfferingInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Service offering')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('category.label')
                            ->label('Category'),
                        TextEntry::make('slug'),
                        TextEntry::make('sort_order')
                            ->formatStateUsing(fn (?int $state): string => number_format($state ?? 0)),
                        TextEntry::make('title'),
                        TextEntry::make('subtitle'),
                        TextEntry::make('price_label')
                            ->placeholder('-'),
                        TextEntry::make('description')
                            ->placeholder('-')
                            ->columnSpanFull(),
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
