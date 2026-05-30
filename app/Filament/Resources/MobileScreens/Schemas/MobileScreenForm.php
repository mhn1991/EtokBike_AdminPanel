<?php

namespace App\Filament\Resources\MobileScreens\Schemas;

use App\Models\MobileScreen;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class MobileScreenForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('App page')
                    ->columns(3)
                    ->schema([
                        Select::make('screen_id')
                            ->label('Screen')
                            ->options(MobileScreen::SCREEN_OPTIONS)
                            ->required()
                            ->unique(ignoreRecord: true),
                        TextInput::make('title')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('version')
                            ->required()
                            ->integer()
                            ->minValue(1)
                            ->default(1)
                            ->helperText('Manual base version. The API also increases this from edit timestamps.'),
                        Toggle::make('hide_title')
                            ->label('Hide page title in app')
                            ->required()
                            ->default(false),
                        Toggle::make('is_active')
                            ->label('Use this page in mobile API')
                            ->required()
                            ->default(true),
                    ]),
            ]);
    }
}
