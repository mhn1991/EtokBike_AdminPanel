<?php

namespace App\Filament\Resources\Programs\Schemas;

use App\Models\Program;
use Filament\Infolists\Components\ColorEntry;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProgramInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Program')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('category.label')
                            ->label('Category'),
                        TextEntry::make('slug'),
                        TextEntry::make('program_state')
                            ->badge()
                            ->formatStateUsing(fn (string $state): string => __(Program::STATE_OPTIONS[$state] ?? $state))
                            ->color(fn (string $state): string => match ($state) {
                                'future' => 'success',
                                'finished' => 'gray',
                                'cancelled' => 'danger',
                                default => 'gray',
                            }),
                        TextEntry::make('title'),
                        TextEntry::make('subtitle'),
                        TextEntry::make('date_value')
                            ->date(),
                        TextEntry::make('date_label'),
                        TextEntry::make('status_label')
                            ->placeholder(__('-')),
                        TextEntry::make('sort_order')
                            ->formatStateUsing(fn (?int $state): string => number_format($state ?? 0)),
                        IconEntry::make('is_active')
                            ->label('Visible in app')
                            ->boolean(),
                    ]),
                Section::make('Detail page')
                    ->schema([
                        TextEntry::make('book_label')
                            ->placeholder(__('-')),
                        TextEntry::make('view_label')
                            ->placeholder(__('-')),
                        TextEntry::make('ad_title')
                            ->placeholder(__('-')),
                        TextEntry::make('advertisement')
                            ->placeholder(__('-'))
                            ->columnSpanFull(),
                        TextEntry::make('details')
                            ->formatStateUsing(fn (mixed $state): string => is_array($state) ? implode(', ', $state) : (string) $state)
                            ->placeholder(__('-'))
                            ->columnSpanFull(),
                        TextEntry::make('gallery_title')
                            ->placeholder(__('-')),
                    ]),
                Section::make('App card and capacity')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('thumbnail_text'),
                        ColorEntry::make('thumbnail_color'),
                        ImageEntry::make('image_url')
                            ->disk('public')
                            ->placeholder(__('-')),
                        TextEntry::make('capacity')
                            ->formatStateUsing(fn (?int $state): string => is_null($state) ? '-' : number_format($state))
                            ->placeholder(__('-')),
                        TextEntry::make('reserved_count')
                            ->formatStateUsing(fn (?int $state): string => number_format($state ?? 0)),
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
