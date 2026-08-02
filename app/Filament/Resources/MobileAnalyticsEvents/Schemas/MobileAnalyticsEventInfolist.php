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
                Section::make(__('Event'))
                    ->columns(3)
                    ->schema([
                        TextEntry::make('event_name')
                            ->label('Event')
                            ->badge()
                            ->formatStateUsing(fn (?string $state): string => MobileAnalyticsEvent::eventLabel($state))
                            ->color(fn (?string $state): string => MobileAnalyticsEvent::eventColor($state)),
                        TextEntry::make('screen_id')
                            ->label('Screen')
                            ->placeholder(__('-')),
                        TextEntry::make('action')
                            ->placeholder(__('-')),
                        TextEntry::make('occurred_at')
                            ->dateTime()
                            ->placeholder(__('-')),
                        TextEntry::make('created_at')
                            ->dateTime(),
                        TextEntry::make('updated_at')
                            ->dateTime(),
                    ]),
                Section::make(__('Device'))
                    ->columns(2)
                    ->schema([
                        TextEntry::make('device_id')
                            ->label('Device ID')
                            ->copyable(),
                        TextEntry::make('session_id')
                            ->label('Session ID')
                            ->copyable()
                            ->placeholder(__('-')),
                        TextEntry::make('platform')
                            ->placeholder(__('-')),
                        TextEntry::make('app_version')
                            ->label('App version')
                            ->placeholder(__('-')),
                        TextEntry::make('ip_address')
                            ->label('IP')
                            ->placeholder(__('-')),
                        TextEntry::make('user_agent')
                            ->label('User agent')
                            ->columnSpanFull()
                            ->placeholder(__('-')),
                    ]),
                Section::make(__('Metadata'))
                    ->schema([
                        KeyValueEntry::make('metadata')
                            ->placeholder(__('No metadata was sent.')),
                    ]),
            ]);
    }
}
