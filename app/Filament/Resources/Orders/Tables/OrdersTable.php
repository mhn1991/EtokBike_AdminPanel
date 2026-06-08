<?php

namespace App\Filament\Resources\Orders\Tables;

use App\Models\Order;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->paginated(false)
            ->striped()
            ->columns([
                TextColumn::make('order_number')
                    ->label('Order')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('customer_name')
                    ->label('Customer')
                    ->description(fn (Order $record): string => collect([$record->customer_phone, $record->customer_email])->filter()->join(' · '))
                    ->searchable()
                    ->wrap(),
                TextColumn::make('customer_email')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
                    })
                    ->sortable(),
                TextColumn::make('payment_status')
                    ->label('Payment')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => Order::PAYMENT_STATUS_OPTIONS[$state] ?? $state)
                    ->color(fn (string $state): string => match ($state) {
                        'paid' => 'success',
                        'failed', 'refunded' => 'danger',
                        default => 'warning',
                    })
                    ->visibleFrom('md')
                    ->sortable(),
                TextColumn::make('fulfillment_method')
                    ->label('Fulfilment')
                    ->formatStateUsing(fn (string $state): string => Order::FULFILLMENT_METHOD_OPTIONS[$state] ?? $state)
                    ->visibleFrom('lg')
                    ->toggleable()
                    ->sortable(),
                TextColumn::make('total')
                    ->label('Total')
                    ->formatStateUsing(fn (?int $state): string => number_format($state ?? 0))
                    ->visibleFrom('md')
                    ->sortable(),
                TextColumn::make('placed_at')
                    ->dateTime()
                    ->visibleFrom('xl')
                    ->toggleable(isToggledHiddenByDefault: true)
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
            ->emptyStateIcon(Heroicon::OutlinedShoppingCart)
            ->emptyStateHeading('No orders yet')
            ->emptyStateDescription('Orders placed from the mobile app will appear here for fulfilment.')
            ->filters([
                SelectFilter::make('status')
                    ->options(Order::STATUS_OPTIONS),
                SelectFilter::make('payment_status')
                    ->options(Order::PAYMENT_STATUS_OPTIONS),
                SelectFilter::make('fulfillment_method')
                    ->options(Order::FULFILLMENT_METHOD_OPTIONS),
            ])
            ->defaultSort('placed_at', 'desc')
            ->recordActions(ActionGroup::make([
                ViewAction::make()
                    ->label('Open details'),
                EditAction::make(),
                Action::make('assignToMe')
                    ->label('Assign to me')
                    ->icon(Heroicon::UserPlus)
                    ->color('gray')
                    ->visible(fn (Order $record): bool => $record->user_id !== auth()->id())
                    ->action(fn (Order $record) => $record->update(['user_id' => auth()->id()]))
                    ->successNotificationTitle('Order assigned to you'),
                Action::make('markPaid')
                    ->label('Mark paid')
                    ->icon(Heroicon::CurrencyDollar)
                    ->color('success')
                    ->visible(fn (Order $record): bool => $record->payment_status !== 'paid')
                    ->action(fn (Order $record) => $record->update(['payment_status' => 'paid']))
                    ->successNotificationTitle('Order marked paid'),
                Action::make('confirm')
                    ->label('Confirm order')
                    ->icon(Heroicon::CheckCircle)
                    ->color('info')
                    ->visible(fn (Order $record): bool => $record->status === 'pending')
                    ->action(fn (Order $record) => $record->update(['status' => 'confirmed']))
                    ->successNotificationTitle('Order confirmed'),
                Action::make('markReady')
                    ->label('Mark ready')
                    ->icon(Heroicon::ArchiveBox)
                    ->color('primary')
                    ->visible(fn (Order $record): bool => in_array($record->status, ['confirmed', 'processing'], true))
                    ->action(fn (Order $record) => $record->update(['status' => 'ready']))
                    ->successNotificationTitle('Order marked ready'),
                Action::make('complete')
                    ->label('Complete')
                    ->icon(Heroicon::CheckBadge)
                    ->color('success')
                    ->visible(fn (Order $record): bool => ! in_array($record->status, ['completed', 'cancelled'], true))
                    ->action(fn (Order $record) => $record->update(['status' => 'completed']))
                    ->successNotificationTitle('Order completed'),
                Action::make('callCustomer')
                    ->label('Call customer')
                    ->icon(Heroicon::Phone)
                    ->color('gray')
                    ->visible(fn (Order $record): bool => filled($record->customer_phone))
                    ->url(fn (Order $record): string => 'tel:'.$record->customer_phone),
            ])
                ->label('Actions')
                ->icon(Heroicon::EllipsisHorizontal)
                ->iconButton()
                ->color('gray'))
            ->recordActionsColumnLabel('')
            ->toolbarActions([]);
    }
}
