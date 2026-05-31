<?php

namespace App\Filament\Resources\CustomerMessages\Schemas;

use App\Models\CustomerMessage;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CustomerMessageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Conversation')
                    ->description('Route the message to the right department and customer account.')
                    ->columns(3)
                    ->schema([
                        Select::make('message_department_id')
                            ->label('Department')
                            ->relationship('department', 'title')
                            ->native(false)
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('user_id')
                            ->label('Linked user')
                            ->relationship('user', 'name')
                            ->native(false)
                            ->searchable()
                            ->preload(),
                        ToggleButtons::make('sender')
                            ->options(CustomerMessage::SENDER_OPTIONS)
                            ->colors([
                                'client' => 'warning',
                                'department' => 'info',
                            ])
                            ->inline()
                            ->required()
                            ->default('client'),
                    ]),
                Section::make('Message')
                    ->columns(2)
                    ->schema([
                        TextInput::make('label')
                            ->required()
                            ->maxLength(255)
                            ->default('Customer message'),
                        TextInput::make('time_label')
                            ->maxLength(255),
                        Textarea::make('text')
                            ->required()
                            ->rows(5)
                            ->columnSpanFull(),
                        Toggle::make('is_unread')
                            ->label('Needs response')
                            ->required()
                            ->default(true),
                    ]),
            ]);
    }
}
