<?php

namespace App\Filament\Resources\Receipts\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Receipt item')
                    ->description('Snapshot line shown on the receipt printout.')
                    ->columns(3)
                    ->schema([
                        Select::make('product_id')
                            ->label('Product')
                            ->relationship('product', 'title')
                            ->native(false)
                            ->searchable()
                            ->preload(),
                        TextInput::make('sku')
                            ->label('SKU')
                            ->maxLength(255),
                        TextInput::make('title')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        TextInput::make('quantity')
                            ->required()
                            ->integer()
                            ->minValue(1)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Get $get, Set $set): mixed => $set('line_total', (int) $get('quantity') * (int) $get('unit_price')))
                            ->default(1),
                        TextInput::make('unit_price')
                            ->required()
                            ->integer()
                            ->minValue(0)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Get $get, Set $set): mixed => $set('line_total', (int) $get('quantity') * (int) $get('unit_price')))
                            ->suffix('IRR')
                            ->default(0),
                        TextInput::make('line_total')
                            ->required()
                            ->disabled()
                            ->dehydrated()
                            ->integer()
                            ->suffix('IRR')
                            ->default(0),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->paginated(false)
            ->striped()
            ->columns([
                TextColumn::make('title')
                    ->description(fn ($record): string => $record->sku ?: '')
                    ->searchable()
                    ->wrap(),
                TextColumn::make('quantity')
                    ->formatStateUsing(fn (?int $state): string => number_format($state ?? 0))
                    ->sortable(),
                TextColumn::make('unit_price')
                    ->formatStateUsing(fn (?int $state): string => number_format($state ?? 0))
                    ->sortable(),
                TextColumn::make('line_total')
                    ->formatStateUsing(fn (?int $state): string => number_format($state ?? 0))
                    ->sortable(),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([]);
    }
}
