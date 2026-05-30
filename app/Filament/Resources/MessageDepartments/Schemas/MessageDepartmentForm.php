<?php

namespace App\Filament\Resources\MessageDepartments\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class MessageDepartmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('slug')
                    ->required(),
                TextInput::make('title')
                    ->required(),
                TextInput::make('subtitle'),
                TextInput::make('thread_title')
                    ->required(),
                TextInput::make('composer_title')
                    ->required(),
                TextInput::make('placeholder'),
                TextInput::make('send_label')
                    ->required()
                    ->default('ارسال پیام'),
                TextInput::make('sort_order')
                    ->required()
                    ->integer()
                    ->minValue(0)
                    ->default(0),
                Toggle::make('is_active')
                    ->required()
                    ->default(true),
            ]);
    }
}
