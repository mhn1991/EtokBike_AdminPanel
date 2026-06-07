<?php

namespace App\Filament\Resources\Programs\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class GalleryItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'galleryItems';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Gallery item')
                    ->description('Photo metadata used when a finished program shows its gallery.')
                    ->columns(3)
                    ->schema([
                        TextInput::make('caption')
                            ->maxLength(255),
                        TextInput::make('sort_order')
                            ->required()
                            ->integer()
                            ->minValue(0)
                            ->default(0),
                        TextInput::make('thumbnail_text')
                            ->required()
                            ->maxLength(255)
                            ->default('PHOTO'),
                        ColorPicker::make('thumbnail_color')
                            ->required()
                            ->hex()
                            ->default('#101114'),
                        FileUpload::make('image_url')
                            ->label('Gallery image')
                            ->disk('public')
                            ->directory('mobile/program-galleries')
                            ->visibility('public')
                            ->image()
                            ->imagePreviewHeight('160')
                            ->openable()
                            ->downloadable()
                            ->maxSize(4096)
                            ->columnSpan(2),
                    ]),
            ]);
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('thumbnail_text'),
                TextEntry::make('thumbnail_color'),
                TextEntry::make('caption')
                    ->placeholder('-'),
                TextEntry::make('image_url')
                    ->placeholder('-'),
                TextEntry::make('sort_order')
                    ->formatStateUsing(fn (?int $state): string => number_format($state ?? 0)),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('caption')
            ->paginated(false)
            ->striped()
            ->columns([
                TextColumn::make('thumbnail_text')
                    ->searchable(),
                ColorColumn::make('thumbnail_color')
                    ->label('Color'),
                TextColumn::make('caption')
                    ->searchable(),
                TextColumn::make('image_url')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('sort_order')
                    ->formatStateUsing(fn (?int $state): string => number_format($state ?? 0))
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
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
