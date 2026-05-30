<?php

namespace App\Filament\Resources\Programs\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ProgramInfolist
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
                TextEntry::make('date_value')
                    ->date(),
                TextEntry::make('date_label'),
                TextEntry::make('program_state'),
                TextEntry::make('status_label')
                    ->placeholder('-'),
                TextEntry::make('book_label')
                    ->placeholder('-'),
                TextEntry::make('view_label')
                    ->placeholder('-'),
                TextEntry::make('ad_title')
                    ->placeholder('-'),
                TextEntry::make('advertisement')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('details')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('gallery_title')
                    ->placeholder('-'),
                TextEntry::make('thumbnail_text'),
                TextEntry::make('thumbnail_color'),
                TextEntry::make('image_url')
                    ->placeholder('-'),
                TextEntry::make('capacity')
                    ->formatStateUsing(fn (?int $state): string => is_null($state) ? '-' : number_format($state))
                    ->placeholder('-'),
                TextEntry::make('reserved_count')
                    ->formatStateUsing(fn (?int $state): string => number_format($state ?? 0)),
                TextEntry::make('sort_order')
                    ->formatStateUsing(fn (?int $state): string => number_format($state ?? 0)),
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
