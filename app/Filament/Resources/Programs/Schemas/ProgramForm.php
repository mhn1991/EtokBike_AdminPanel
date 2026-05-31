<?php

namespace App\Filament\Resources\Programs\Schemas;

use App\Models\Program;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ProgramForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Program')
                    ->description('Event listing information and publication status.')
                    ->columns(3)
                    ->schema([
                        Select::make('program_category_id')
                            ->label('Category')
                            ->relationship('category', 'label')
                            ->native(false)
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('slug')
                            ->required()
                            ->helperText('Stable program ID used by the mobile app.')
                            ->maxLength(255),
                        ToggleButtons::make('program_state')
                            ->options(Program::STATE_OPTIONS)
                            ->colors([
                                'future' => 'success',
                                'finished' => 'gray',
                                'cancelled' => 'danger',
                            ])
                            ->inline()
                            ->required()
                            ->default('future'),
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
                        DatePicker::make('date_value')
                            ->label('Program date')
                            ->native(false)
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
                    ->description('Visual treatment for program cards in the mobile app.')
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
                        TextInput::make('image_url')
                            ->url()
                            ->maxLength(255),
                    ]),
                Section::make('Detail page')
                    ->description('Copy, tags, and labels used after a customer opens the program.')
                    ->schema([
                        TextInput::make('ad_title')
                            ->maxLength(255),
                        Textarea::make('advertisement')
                            ->rows(4)
                            ->columnSpanFull(),
                        TagsInput::make('details')
                            ->separator(',')
                            ->helperText('Add short detail chips such as route length, difficulty, or meeting point.')
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
                    ->description('Optional availability numbers shown to staff.')
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
