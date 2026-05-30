<?php

namespace App\Filament\Resources\CustomerMessages\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class CustomerMessageInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('message_department_id')
                    ->numeric(),
                TextEntry::make('user.name')
                    ->label('User')
                    ->placeholder('-'),
                TextEntry::make('sender'),
                TextEntry::make('label'),
                TextEntry::make('text')
                    ->columnSpanFull(),
                TextEntry::make('time_label')
                    ->placeholder('-'),
                IconEntry::make('is_unread')
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
