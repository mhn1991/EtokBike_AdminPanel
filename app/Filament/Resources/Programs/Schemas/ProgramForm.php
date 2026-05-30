<?php

namespace App\Filament\Resources\Programs\Schemas;

use App\Models\Program;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProgramForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Program')
                    ->columns(3)
                    ->schema([
                        Select::make('program_category_id')
                            ->label('Category')
                            ->relationship('category', 'label')
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('slug')
                            ->required()
                            ->maxLength(255),
                        Select::make('program_state')
                            ->options(Program::STATE_OPTIONS)
                            ->required()
                            ->default('future'),
                        TextInput::make('title')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('subtitle')
                            ->required()
                            ->maxLength(255),
                        DatePicker::make('date_value')
                            ->required(),
                        TextInput::make('date_label')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('status_label')
                            ->maxLength(255),
                        TextInput::make('sort_order')
                            ->required()
                            ->integer()
                            ->minValue(0)
                            ->default(0),
                        Toggle::make('is_active')
                            ->required()
                            ->default(true),
                    ]),
                Section::make('App card')
                    ->columns(3)
                    ->schema([
                        TextInput::make('thumbnail_text')
                            ->required()
                            ->maxLength(255)
                            ->default('ETOK'),
                        TextInput::make('thumbnail_color')
                            ->required()
                            ->maxLength(16)
                            ->default('#101114'),
                        TextInput::make('image_url')
                            ->url()
                            ->maxLength(255),
                    ]),
                Section::make('Detail page')
                    ->schema([
                        TextInput::make('ad_title')
                            ->maxLength(255),
                        Textarea::make('advertisement')
                            ->rows(4)
                            ->columnSpanFull(),
                        TagsInput::make('details')
                            ->separator(',')
                            ->columnSpanFull(),
                        TextInput::make('book_label')
                            ->helperText('Only used for future programs.')
                            ->maxLength(255),
                        TextInput::make('view_label')
                            ->helperText('Only used for finished programs.')
                            ->maxLength(255),
                        TextInput::make('gallery_title')
                            ->helperText('Only used for finished programs.')
                            ->maxLength(255),
                    ]),
                Section::make('Capacity')
                    ->columns(2)
                    ->schema([
                        TextInput::make('capacity')
                            ->integer()
                            ->minValue(0),
                        TextInput::make('reserved_count')
                            ->required()
                            ->integer()
                            ->minValue(0)
                            ->default(0),
                    ]),
            ]);
    }
}
