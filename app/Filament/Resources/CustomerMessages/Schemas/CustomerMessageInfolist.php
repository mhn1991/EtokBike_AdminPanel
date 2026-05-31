<?php

namespace App\Filament\Resources\CustomerMessages\Schemas;

use App\Models\CustomerMessage;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CustomerMessageInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Conversation')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('department.title')
                            ->label('Department'),
                        TextEntry::make('user.name')
                            ->label('User')
                            ->placeholder('-'),
                        TextEntry::make('sender')
                            ->badge()
                            ->formatStateUsing(fn (string $state): string => CustomerMessage::SENDER_OPTIONS[$state] ?? $state)
                            ->color(fn (string $state): string => $state === 'client' ? 'warning' : 'info'),
                        IconEntry::make('is_unread')
                            ->label('Needs response')
                            ->boolean(),
                        TextEntry::make('time_label')
                            ->placeholder('-'),
                    ]),
                Section::make('Message')
                    ->schema([
                        TextEntry::make('label'),
                        TextEntry::make('text')
                            ->columnSpanFull(),
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
