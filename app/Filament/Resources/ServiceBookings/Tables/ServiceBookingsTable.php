<?php

namespace App\Filament\Resources\ServiceBookings\Tables;

use App\Models\ServiceBooking;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class ServiceBookingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->striped()
            ->columns([
                TextColumn::make('user.name')
                    ->label('User')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('customer_name')
                    ->label('Customer')
                    ->description(fn (ServiceBooking $record): ?string => $record->customer_phone)
                    ->searchable()
                    ->wrap(),
                TextColumn::make('customer_email')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('service_type')
                    ->label('Service')
                    ->searchable(),
                TextColumn::make('bike_label')
                    ->searchable()
                    ->visibleFrom('lg')
                    ->toggleable(),
                TextColumn::make('preferred_time')
                    ->visibleFrom('md')
                    ->searchable(),
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => __(ServiceBooking::STATUS_OPTIONS[$state] ?? $state))
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'confirmed' => 'info',
                        'in_progress' => 'primary',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    })
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
            ->emptyStateIcon(Heroicon::OutlinedWrenchScrewdriver)
            ->emptyStateHeading(__('No service bookings yet'))
            ->emptyStateDescription(__('Customer service requests will appear here for workshop triage.'))
            ->filters([
                SelectFilter::make('status')
                    ->options(\App\Support\Admin\FilamentLocalization::options(ServiceBooking::STATUS_OPTIONS)),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions(ActionGroup::make([
                ViewAction::make()
                    ->label('Open details'),
                EditAction::make(),
                Action::make('assignToMe')
                    ->label('Assign to me')
                    ->icon(Heroicon::UserPlus)
                    ->color('gray')
                    ->visible(fn (ServiceBooking $record): bool => $record->user_id !== auth()->id())
                    ->action(fn (ServiceBooking $record) => $record->update(['user_id' => auth()->id()]))
                    ->successNotificationTitle(__('Booking assigned to you')),
                Action::make('confirm')
                    ->label('Confirm booking')
                    ->icon(Heroicon::CheckCircle)
                    ->color('info')
                    ->visible(fn (ServiceBooking $record): bool => $record->status === 'pending')
                    ->action(fn (ServiceBooking $record) => $record->update(['status' => 'confirmed']))
                    ->successNotificationTitle(__('Booking confirmed')),
                Action::make('startWork')
                    ->label('Start workshop work')
                    ->icon(Heroicon::WrenchScrewdriver)
                    ->color('primary')
                    ->visible(fn (ServiceBooking $record): bool => in_array($record->status, ['pending', 'confirmed'], true))
                    ->action(fn (ServiceBooking $record) => $record->update(['status' => 'in_progress']))
                    ->successNotificationTitle(__('Booking moved to in progress')),
                Action::make('complete')
                    ->label('Complete service')
                    ->icon(Heroicon::CheckBadge)
                    ->color('success')
                    ->visible(fn (ServiceBooking $record): bool => ! in_array($record->status, ['completed', 'cancelled'], true))
                    ->action(fn (ServiceBooking $record) => $record->update(['status' => 'completed']))
                    ->successNotificationTitle(__('Service booking completed')),
                Action::make('callCustomer')
                    ->label('Call customer')
                    ->icon(Heroicon::Phone)
                    ->color('gray')
                    ->visible(fn (ServiceBooking $record): bool => filled($record->customer_phone))
                    ->url(fn (ServiceBooking $record): string => 'tel:'.$record->customer_phone),
            ])
                ->label('Actions')
                ->icon(Heroicon::EllipsisHorizontal)
                ->iconButton()
                ->color('gray'))
            ->recordActionsColumnLabel('')
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('bulkConfirm')
                        ->label('Confirm booking')
                        ->icon(Heroicon::CheckCircle)
                        ->color('info')
                        ->action(fn (Collection $records) => $records->each(function (ServiceBooking $record) {
                            if ($record->status === 'pending') {
                                $record->update(['status' => 'confirmed']);
                            }
                        }))
                        ->deselectRecordsAfterCompletion()
                        ->successNotificationTitle(__('Selected bookings confirmed')),
                ])->label('Bulk actions'),
            ]);
    }
}
