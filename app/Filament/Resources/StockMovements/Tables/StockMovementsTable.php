<?php

namespace App\Filament\Resources\StockMovements\Tables;

use App\Models\StockMovement;
use Filament\Actions\ViewAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class StockMovementsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->paginated(false)
            ->striped()
            ->columns([
                TextColumn::make('created_at')
                    ->label('Time')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('product.title')
                    ->label('Product')
                    ->description(fn (StockMovement $record): string => collect([
                        $record->product?->sku,
                        $record->product?->warehouse_location,
                    ])->filter()->join(' · '))
                    ->searchable()
                    ->wrap(),
                TextColumn::make('type')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => StockMovement::TYPE_OPTIONS[$state] ?? $state)
                    ->color(fn (string $state): string => match ($state) {
                        'stock_in', 'sale_return', 'return' => 'success',
                        'sale', 'manual_removal', 'damage' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('quantity_delta')
                    ->label('Change')
                    ->formatStateUsing(fn (?int $state): string => number_format($state ?? 0))
                    ->color(fn (?int $state): string => ($state ?? 0) < 0 ? 'danger' : 'success')
                    ->sortable(),
                TextColumn::make('new_quantity')
                    ->label('On hand')
                    ->formatStateUsing(fn (?int $state): string => number_format($state ?? 0))
                    ->sortable(),
                TextColumn::make('reference')
                    ->placeholder('-')
                    ->searchable()
                    ->visibleFrom('lg'),
                TextColumn::make('reason')
                    ->placeholder('-')
                    ->searchable()
                    ->wrap()
                    ->visibleFrom('xl'),
                TextColumn::make('order.order_number')
                    ->label('Order')
                    ->placeholder('-')
                    ->searchable()
                    ->visibleFrom('lg'),
                TextColumn::make('user.name')
                    ->label('User')
                    ->placeholder('-')
                    ->visibleFrom('xl'),
            ])
            ->emptyStateIcon(Heroicon::OutlinedArchiveBox)
            ->emptyStateHeading('No stock movements yet')
            ->emptyStateDescription('Manual adjustments and order sales will appear here as an audit trail.')
            ->filters([
                SelectFilter::make('product_id')
                    ->label('Product')
                    ->relationship('product', 'title')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('type')
                    ->options(StockMovement::TYPE_OPTIONS),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                ViewAction::make(),
            ])
            ->toolbarActions([]);
    }
}
