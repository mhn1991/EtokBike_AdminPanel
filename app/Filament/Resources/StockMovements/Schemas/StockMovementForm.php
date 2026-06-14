<?php

namespace App\Filament\Resources\StockMovements\Schemas;

use App\Models\StockMovement;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class StockMovementForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Adjustment')
                    ->description('Record manual warehouse changes. Sales are recorded automatically from orders.')
                    ->columns(3)
                    ->schema([
                        Select::make('product_id')
                            ->label('Product')
                            ->relationship('product', 'title')
                            ->native(false)
                            ->searchable()
                            ->preload()
                            ->required()
                            ->columnSpan(2),
                        Select::make('movement_type')
                            ->label('Type')
                            ->options([
                                'stock_in' => StockMovement::TYPE_OPTIONS['stock_in'],
                                'manual_removal' => StockMovement::TYPE_OPTIONS['manual_removal'],
                                'return' => StockMovement::TYPE_OPTIONS['return'],
                                'damage' => StockMovement::TYPE_OPTIONS['damage'],
                                'adjustment' => StockMovement::TYPE_OPTIONS['adjustment'],
                            ])
                            ->native(false)
                            ->required()
                            ->default('stock_in'),
                        ToggleButtons::make('adjustment_direction')
                            ->label('Adjustment direction')
                            ->options([
                                'in' => 'Increase',
                                'out' => 'Decrease',
                            ])
                            ->colors([
                                'in' => 'success',
                                'out' => 'danger',
                            ])
                            ->inline()
                            ->default('in'),
                        TextInput::make('quantity')
                            ->required()
                            ->integer()
                            ->minValue(1)
                            ->default(1),
                        TextInput::make('reference')
                            ->placeholder('Supplier invoice, count ID, or note')
                            ->maxLength(255),
                        Textarea::make('reason')
                            ->rows(4)
                            ->maxLength(255)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
