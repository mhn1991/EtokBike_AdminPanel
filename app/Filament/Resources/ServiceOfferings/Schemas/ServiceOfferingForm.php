<?php

namespace App\Filament\Resources\ServiceOfferings\Schemas;

use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ServiceOfferingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Service offering')
                    ->description('The service card customers see in the mobile app.')
                    ->columns(3)
                    ->schema([
                        Select::make('service_category_id')
                            ->label('Category')
                            ->relationship('category', 'label')
                            ->native(false)
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('slug')
                            ->required()
                            ->helperText('Stable service ID used by the mobile app.')
                            ->maxLength(255),
                        TextInput::make('sort_order')
                            ->required()
                            ->integer()
                            ->minValue(0)
                            ->default(0),
                        TextInput::make('title')
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Get $get, Set $set, ?string $state): void {
                                if (blank($get('slug'))) {
                                    $set('slug', Str::slug($state ?? ''));
                                }
                            })
                            ->maxLength(255),
                        TextInput::make('subtitle')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('price_label')
                            ->maxLength(255),
                        Textarea::make('description')
                            ->rows(4)
                            ->columnSpanFull(),
                        Toggle::make('is_active')
                            ->label('Visible in app')
                            ->required()
                            ->default(true),
                    ]),
                Section::make('App card')
                    ->description('Thumbnail treatment for service lists.')
                    ->columns(3)
                    ->schema([
                        TextInput::make('thumbnail_text')
                            ->required()
                            ->maxLength(255)
                            ->default('ETOK'),
                        ColorPicker::make('thumbnail_color')
                            ->required()
                            ->hex()
                            ->default('#101114'),
                        FileUpload::make('image_url')
                            ->label('Service image')
                            ->disk('public')
                            ->directory('mobile/services')
                            ->visibility('public')
                            ->image()
                            ->imagePreviewHeight('160')
                            ->openable()
                            ->downloadable()
                            ->maxSize(4096),
                    ]),
            ]);
    }
}
