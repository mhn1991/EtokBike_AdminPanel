<?php

namespace App\Filament\Resources\ServiceOfferings\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ServiceOfferingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('service_category_id')
                    ->label('Category')
                    ->relationship('category', 'label')
                    ->searchable()
                    ->preload()
                    ->required(),
                TextInput::make('slug')
                    ->required(),
                TextInput::make('title')
                    ->required(),
                TextInput::make('subtitle')
                    ->required(),
                Textarea::make('description')
                    ->columnSpanFull(),
                TextInput::make('price_label'),
                TextInput::make('thumbnail_text')
                    ->required()
                    ->default('ETOK'),
                TextInput::make('thumbnail_color')
                    ->required()
                    ->default('#101114'),
                TextInput::make('image_url')
                    ->url(),
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
