<?php

namespace App\Filament\Resources\MobileAnalyticsEvents\Schemas;

use App\Models\MobileAnalyticsEvent;
use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class MobileAnalyticsEventInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Event')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('event_name')
                            ->label('Event')
                            ->badge()
                            ->formatStateUsing(fn (?string $state): string => MobileAnalyticsEvent::eventLabel($state))
                            ->color(fn (?string $state): string => MobileAnalyticsEvent::eventColor($state)),
                        TextEntry::make('screen_id')
                            ->label('Screen')
                            ->placeholder('-'),
                        TextEntry::make('action')
                            ->placeholder('-'),
                        TextEntry::make('occurred_at')
                            ->dateTime()
                            ->placeholder('-'),
                        TextEntry::make('created_at')
                            ->dateTime(),
                        TextEntry::make('updated_at')
                            ->dateTime(),
                    ]),
                Section::make('Device')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('device_id')
                            ->label('Device ID')
                            ->copyable(),
                        TextEntry::make('session_id')
                            ->label('Session ID')
                            ->copyable()
                            ->placeholder('-'),
                        TextEntry::make('platform')
                            ->placeholder('-'),
                        TextEntry::make('app_version')
                            ->label('App version')
                            ->placeholder('-'),
                        TextEntry::make('ip_address')
                            ->label('IP')
                            ->placeholder('-'),
                        TextEntry::make('user_agent')
                            ->label('User agent')
                            ->columnSpanFull()
                            ->placeholder('-'),
                    ]),
                Section::make('Metadata')
                    ->schema([
                        KeyValueEntry::make('metadata')
                            ->placeholder('No metadata was sent.'),
                    ]),
            ]);
    }
}
