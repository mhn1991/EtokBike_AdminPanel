<?php

namespace App\Filament\Resources\CustomerMessages\Schemas;

use App\Models\CustomerMessage;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CustomerMessageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('message_department_id')
                    ->label('Department')
                    ->relationship('department', 'title')
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),
                Select::make('sender')
                    ->options(CustomerMessage::SENDER_OPTIONS)
                    ->required()
                    ->default('client'),
                TextInput::make('label')
                    ->required()
                    ->default(false),
                Textarea::make('text')
                    ->required()
                    ->columnSpanFull(),
                TextInput::make('time_label'),
                Toggle::make('is_unread')
                    ->required(),
            ]);
    }
}
