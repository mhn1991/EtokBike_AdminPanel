<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Orders\OrderResource;
use App\Models\Order;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class RecentOrdersWidget extends TableWidget
{
    protected static bool $isLazy = false;

    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Recent orders')
            ->description('The newest customer purchases, ready for quick triage.')
            ->query(fn (): Builder => Order::query()->latest('placed_at')->limit(8))
            ->paginated(false)
            ->striped()
            ->columns([
                TextColumn::make('order_number')
                    ->label('Order')
                    ->searchable(),
                TextColumn::make('customer_name')
                    ->label('Customer')
                    ->searchable(),
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => Order::STATUS_OPTIONS[$state] ?? $state)
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'confirmed', 'processing' => 'info',
                        'ready' => 'primary',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('payment_status')
                    ->badge()
                    ->visibleFrom('md')
                    ->formatStateUsing(fn (string $state): string => Order::PAYMENT_STATUS_OPTIONS[$state] ?? $state)
                    ->color(fn (string $state): string => match ($state) {
                        'paid' => 'success',
                        'failed', 'refunded' => 'danger',
                        default => 'warning',
                    }),
                TextColumn::make('total')
                    ->visibleFrom('md')
                    ->formatStateUsing(fn (?int $state): string => number_format($state ?? 0)),
                TextColumn::make('placed_at')
                    ->visibleFrom('lg')
                    ->dateTime(),
            ])
            ->recordUrl(fn (Order $record): string => OrderResource::getUrl('view', ['record' => $record]));
    }
}
