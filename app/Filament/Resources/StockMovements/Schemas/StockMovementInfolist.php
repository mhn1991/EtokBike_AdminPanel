<?php

namespace App\Filament\Resources\StockMovements\Schemas;

use App\Models\StockMovement;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class StockMovementInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Movement')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('product.title')
                            ->label('Product'),
                        TextEntry::make('type')
                            ->badge()
                            ->formatStateUsing(fn (string $state): string => __(StockMovement::TYPE_OPTIONS[$state] ?? $state))
                            ->color(fn (string $state): string => match ($state) {
                                'stock_in', 'sale_return', 'return' => 'success',
                                'sale', 'manual_removal', 'damage' => 'danger',
                                default => 'gray',
                            }),
                        TextEntry::make('quantity_delta')
                            ->label('Change')
                            ->formatStateUsing(fn (?int $state): string => number_format($state ?? 0)),
                        TextEntry::make('previous_quantity')
                            ->label('Previous')
                            ->formatStateUsing(fn (?int $state): string => number_format($state ?? 0)),
                        TextEntry::make('new_quantity')
                            ->label('New')
                            ->formatStateUsing(fn (?int $state): string => number_format($state ?? 0)),
                        TextEntry::make('reference')
                            ->placeholder(__('-')),
                        TextEntry::make('reason')
                            ->placeholder(__('-'))
                            ->columnSpanFull(),
                    ]),
                Section::make('Linked records')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('order.order_number')
                            ->label('Order')
                            ->placeholder(__('-')),
                        TextEntry::make('orderItem.title')
                            ->label('Order item')
                            ->placeholder(__('-')),
                        TextEntry::make('user.name')
                            ->label('User')
                            ->placeholder(__('-')),
                        TextEntry::make('created_at')
                            ->dateTime()
                            ->placeholder(__('-')),
                    ]),
            ]);
    }
}
