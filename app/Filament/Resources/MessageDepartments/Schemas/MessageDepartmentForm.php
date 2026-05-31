<?php

namespace App\Filament\Resources\MessageDepartments\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class MessageDepartmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Department')
                    ->description('Controls how this support department appears in the app.')
                    ->columns(3)
                    ->schema([
                        TextInput::make('title')
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Get $get, Set $set, ?string $state): void {
                                if (blank($get('slug'))) {
                                    $set('slug', Str::slug($state ?? ''));
                                }
                            })
                            ->maxLength(255),
                        TextInput::make('slug')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('sort_order')
                            ->required()
                            ->integer()
                            ->minValue(0)
                            ->default(0),
                        TextInput::make('subtitle')
                            ->maxLength(255),
                        Toggle::make('is_active')
                            ->label('Visible in app')
                            ->required()
                            ->default(true),
                    ]),
                Section::make('Composer copy')
                    ->description('Text customers see when opening and sending a message.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('thread_title')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('composer_title')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('placeholder')
                            ->maxLength(255),
                        TextInput::make('send_label')
                            ->required()
                            ->maxLength(255)
                            ->default('ارسال پیام'),
                    ]),
            ]);
    }
}
