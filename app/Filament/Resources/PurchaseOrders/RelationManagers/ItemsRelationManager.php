<?php

namespace App\Filament\Resources\PurchaseOrders\RelationManagers;

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
                Section::make('Purchase item')
                    ->description('Quantities are captured in the selected purchase unit.')
                    ->columns(3)
                    ->schema([
                        Select::make('product_id')
                            ->label('Product')
                            ->relationship('product', 'title')
                            ->native(false)
                            ->searchable()
                            ->preload(),
                        Select::make('product_unit_id')
                            ->label('Unit')
                            ->relationship('productUnit', 'name')
                            ->native(false)
                            ->searchable()
                            ->preload(),
                        TextInput::make('sku')
                            ->label('SKU')
                            ->maxLength(255),
                        TextInput::make('description')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        TextInput::make('quantity')
                            ->required()
                            ->integer()
                            ->minValue(1)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Get $get, Set $set): mixed => $set('line_total', (int) $get('quantity') * (int) $get('unit_cost')))
                            ->default(1),
                        TextInput::make('unit_cost')
                            ->required()
                            ->integer()
                            ->minValue(0)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Get $get, Set $set): mixed => $set('line_total', (int) $get('quantity') * (int) $get('unit_cost')))
                            ->suffix('IRR')
                            ->default(0),
                        TextInput::make('line_total')
                            ->required()
                            ->disabled()
                            ->dehydrated()
                            ->integer()
                            ->suffix('IRR')
                            ->default(0),
                        TextInput::make('received_quantity')
                            ->required()
                            ->integer()
                            ->minValue(0)
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
                TextColumn::make('description')
                    ->description(fn ($record): string => collect([$record->sku, $record->productUnit?->name])->filter()->join(' · '))
                    ->searchable()
                    ->wrap(),
                TextColumn::make('quantity')
                    ->formatStateUsing(fn (?int $state): string => number_format($state ?? 0))
                    ->sortable(),
                TextColumn::make('received_quantity')
                    ->label('Received')
                    ->formatStateUsing(fn (?int $state): string => number_format($state ?? 0))
                    ->sortable(),
                TextColumn::make('unit_cost')
                    ->formatStateUsing(fn (?int $state): string => number_format($state ?? 0))
                    ->visibleFrom('md')
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
