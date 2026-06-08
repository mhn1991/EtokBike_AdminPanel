<?php

namespace App\Filament\Resources\MobileScreens\RelationManagers;

use App\Models\MobileScreenSection;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\CodeEditor;
use Filament\Forms\Components\CodeEditor\Enums\Language;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SectionsRelationManager extends RelationManager
{
    protected static string $relationship = 'sections';

    private const STRUCTURED_DATA_TYPES = [
        'hero',
        'category_grid',
        'business_info',
        'service_booking_form',
        'profile_summary',
        'checkout_note',
        'cart_summary',
    ];

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Section')
                    ->columns(3)
                    ->schema([
                        TextInput::make('section_id')
                            ->label('Section ID')
                            ->required()
                            ->maxLength(255),
                        Select::make('type')
                            ->options(MobileScreenSection::TYPE_OPTIONS)
                            ->native(false)
                            ->searchable()
                            ->live()
                            ->required(),
                        TextInput::make('sort_order')
                            ->required()
                            ->integer()
                            ->minValue(0)
                            ->default(0),
                        Toggle::make('is_active')
                            ->label('Visible in app')
                            ->required()
                            ->default(true),
                    ]),
                Section::make('Hero content')
                    ->description('Structured fields for the hero renderer.')
                    ->columns(3)
                    ->visible(fn (Get $get): bool => $get('type') === 'hero')
                    ->schema([
                        TextInput::make('data.visual')
                            ->label('Visual key'),
                        TextInput::make('data.eyebrow')
                            ->label('Eyebrow'),
                        TextInput::make('data.title')
                            ->label('Title')
                            ->required(fn (Get $get): bool => $get('type') === 'hero'),
                        Textarea::make('data.subtitle')
                            ->label('Subtitle')
                            ->rows(3)
                            ->columnSpanFull(),
                        TextInput::make('data.featureTitle')
                            ->label('Feature title'),
                        TextInput::make('data.featureSubtitle')
                            ->label('Feature subtitle'),
                        TextInput::make('data.featurePrice')
                            ->label('Feature label'),
                        TextInput::make('data.primaryActionLabel')
                            ->label('Primary action'),
                        TextInput::make('data.primaryTarget')
                            ->label('Primary target'),
                        TextInput::make('data.secondaryActionLabel')
                            ->label('Secondary action'),
                        TextInput::make('data.secondaryTarget')
                            ->label('Secondary target'),
                        Repeater::make('data.stats')
                            ->label('Stats')
                            ->defaultItems(0)
                            ->grid(2)
                            ->schema([
                                TextInput::make('value')
                                    ->required(),
                                TextInput::make('label')
                                    ->required(),
                            ])
                            ->columnSpanFull(),
                    ]),
                Section::make('Card content')
                    ->description('Structured cards for shortcut grids and business information sections.')
                    ->columns(2)
                    ->visible(fn (Get $get): bool => in_array($get('type'), ['category_grid', 'business_info'], true))
                    ->schema([
                        TextInput::make('data.title')
                            ->label('Section title')
                            ->required(fn (Get $get): bool => in_array($get('type'), ['category_grid', 'business_info'], true)),
                        TextInput::make('data.subtitle')
                            ->label('Section subtitle'),
                        Repeater::make('data.items')
                            ->label('Cards')
                            ->defaultItems(0)
                            ->grid(2)
                            ->schema([
                                TextInput::make('badge'),
                                TextInput::make('title')
                                    ->required(),
                                TextInput::make('subtitle'),
                                TextInput::make('target')
                                    ->helperText('Mobile screen id such as shop, services, messages, or account.'),
                                Textarea::make('description')
                                    ->rows(2)
                                    ->columnSpanFull(),
                                TextInput::make('price')
                                    ->label('Badge or price text'),
                            ])
                            ->columnSpanFull(),
                    ]),
                Section::make('Service booking form')
                    ->description('Labels and option lists used by the mobile booking form.')
                    ->columns(3)
                    ->visible(fn (Get $get): bool => $get('type') === 'service_booking_form')
                    ->schema([
                        TextInput::make('data.title')
                            ->label('Title')
                            ->required(fn (Get $get): bool => $get('type') === 'service_booking_form'),
                        TextInput::make('data.serviceLabel')
                            ->label('Service field label'),
                        TextInput::make('data.bikeLabel')
                            ->label('Bike field label'),
                        TextInput::make('data.timeLabel')
                            ->label('Time field label'),
                        TextInput::make('data.submitLabel')
                            ->label('Submit label'),
                        Textarea::make('data.problemPlaceholder')
                            ->label('Problem placeholder')
                            ->rows(2)
                            ->columnSpanFull(),
                        TagsInput::make('data.services')
                            ->label('Service options')
                            ->columnSpanFull(),
                        TagsInput::make('data.bikes')
                            ->label('Bike options')
                            ->columnSpanFull(),
                        TagsInput::make('data.timeSlots')
                            ->label('Time slot options')
                            ->columnSpanFull(),
                    ]),
                Section::make('Basic copy')
                    ->description('Simple copy blocks that do not need custom JSON editing.')
                    ->columns(2)
                    ->visible(fn (Get $get): bool => in_array($get('type'), ['profile_summary', 'checkout_note', 'cart_summary'], true))
                    ->schema([
                        TextInput::make('data.title')
                            ->label('Title')
                            ->required(fn (Get $get): bool => in_array($get('type'), ['profile_summary', 'checkout_note', 'cart_summary'], true)),
                        TextInput::make('data.subtitle')
                            ->label('Subtitle'),
                        TextInput::make('data.emptyStateText')
                            ->label('Empty state text')
                            ->columnSpanFull(),
                    ]),
                Section::make('Raw data payload')
                    ->description('Use raw JSON only for complex section types that do not yet have structured fields.')
                    ->visible(fn (Get $get): bool => ! self::hasStructuredDataEditor((string) $get('type')))
                    ->schema([
                        CodeEditor::make('data_json')
                            ->label('Data')
                            ->language(Language::Json)
                            ->required(fn (Get $get): bool => ! self::hasStructuredDataEditor((string) $get('type')))
                            ->dehydrated(fn (Get $get): bool => ! self::hasStructuredDataEditor((string) $get('type')))
                            ->rules(['json'])
                            ->columnSpanFull()
                            ->helperText('This is the section data consumed by the Android renderer.'),
                    ]),
                Section::make('Advanced layout and style')
                    ->description('Renderer-specific JSON for layout and styling. Most routine copy edits do not need this.')
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        CodeEditor::make('layout_json')
                            ->label('Layout')
                            ->language(Language::Json)
                            ->required()
                            ->rules(['json'])
                            ->columnSpanFull(),
                        CodeEditor::make('style_json')
                            ->label('Style')
                            ->language(Language::Json)
                            ->required()
                            ->rules(['json'])
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('section_id')
                    ->label('Section ID'),
                TextEntry::make('type'),
                TextEntry::make('sort_order')
                    ->formatStateUsing(fn (?int $state): string => number_format($state ?? 0)),
                TextEntry::make('data_json')
                    ->label('Data')
                    ->columnSpanFull(),
                TextEntry::make('layout_json')
                    ->label('Layout')
                    ->columnSpanFull(),
                TextEntry::make('style_json')
                    ->label('Style')
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('section_id')
            ->paginated(false)
            ->striped()
            ->columns([
                TextColumn::make('sort_order')
                    ->label('#')
                    ->formatStateUsing(fn (?int $state): string => number_format($state ?? 0))
                    ->sortable(),
                TextColumn::make('section_id')
                    ->label('Section ID')
                    ->searchable(),
                TextColumn::make('type')
                    ->badge()
                    ->searchable(),
                IconColumn::make('is_active')
                    ->label('Visible')
                    ->boolean(),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([]);
    }

    private static function hasStructuredDataEditor(string $type): bool
    {
        return in_array($type, self::STRUCTURED_DATA_TYPES, true);
    }
}
