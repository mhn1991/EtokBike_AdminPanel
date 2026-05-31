<?php

namespace App\Filament\Resources\MobileScreens\Schemas;

use App\Models\MobileScreen;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class MobileScreenInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('App page')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('screen_id')
                            ->label('Screen'),
                        TextEntry::make('title'),
                        TextEntry::make('version')
                            ->formatStateUsing(fn (?int $state): string => number_format($state ?? 0)),
                        TextEntry::make('sections_count')
                            ->state(fn (MobileScreen $record): int => $record->sections()->count())
                            ->label('Sections'),
                        IconEntry::make('hide_title')
                            ->label('Hide title')
                            ->boolean(),
                        IconEntry::make('is_active')
                            ->label('API active')
                            ->boolean(),
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
