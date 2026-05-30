<?php

namespace App\Filament\Resources\MobileScreens\RelationManagers;

use App\Models\MobileScreenSection;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\CodeEditor;
use Filament\Forms\Components\CodeEditor\Enums\Language;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SectionsRelationManager extends RelationManager
{
    protected static string $relationship = 'sections';

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
                            ->searchable()
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
                Section::make('JSON payload')
                    ->schema([
                        CodeEditor::make('data_json')
                            ->label('Data')
                            ->language(Language::Json)
                            ->required()
                            ->rules(['json'])
                            ->columnSpanFull()
                            ->helperText('This is the section data consumed by the Android renderer.'),
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
}
