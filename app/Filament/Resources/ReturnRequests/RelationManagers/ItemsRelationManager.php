<?php

namespace App\Filament\Resources\ReturnRequests\RelationManagers;

use App\Models\ReturnRequestItem;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Returned item')
                    ->description('Set item condition and whether it should go back into sellable stock.')
                    ->columns(3)
                    ->schema([
                        Select::make('order_item_id')
                            ->label('Order item')
                            ->relationship('orderItem', 'title')
                            ->native(false)
                            ->searchable()
                            ->preload(),
                        Select::make('product_id')
                            ->label('Product')
                            ->relationship('product', 'title')
                            ->native(false)
                            ->searchable()
                            ->preload(),
                        Select::make('condition')
                            ->options(ReturnRequestItem::CONDITION_OPTIONS)
                            ->native(false)
                            ->required()
                            ->default('inspection'),
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
                        Toggle::make('should_restock')
                            ->label('Restock when received')
                            ->default(false),
                        Textarea::make('notes')
                            ->rows(3)
                            ->columnSpanFull(),
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
                    ->searchable()
                    ->wrap(),
                TextColumn::make('quantity')
                    ->formatStateUsing(fn (?int $state): string => number_format($state ?? 0))
                    ->sortable(),
                TextColumn::make('condition')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => ReturnRequestItem::CONDITION_OPTIONS[$state] ?? $state)
                    ->color(fn (string $state): string => match ($state) {
                        'sellable' => 'success',
                        'damaged' => 'danger',
                        default => 'warning',
                    })
                    ->sortable(),
                IconColumn::make('should_restock')
                    ->label('Restock')
                    ->boolean(),
                TextColumn::make('line_total')
                    ->formatStateUsing(fn (?int $state): string => number_format($state ?? 0))
                    ->visibleFrom('md')
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
