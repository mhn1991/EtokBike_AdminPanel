<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\ServiceBookings\ServiceBookingResource;
use App\Models\ServiceBooking;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class ServiceQueueWidget extends TableWidget
{
    protected static bool $isLazy = false;

    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = [
        'md' => 1,
        'xl' => 1,
    ];

    public function table(Table $table): Table
    {
        $activeStatuses = ['pending', 'confirmed', 'in_progress'];

        return $table
            ->heading('Service queue')
            ->description('Bookings that still need workshop action.')
            ->query(fn (): Builder => ServiceBooking::query()
                ->whereIn('status', $activeStatuses)
                ->latest()
                ->limit(6))
            ->paginated(false)
            ->striped()
            ->columns([
                TextColumn::make('customer_name')
                    ->label('Customer')
                    ->description(fn (ServiceBooking $record): ?string => $record->customer_phone)
                    ->wrap(),
                TextColumn::make('service_type')
                    ->label('Service')
                    ->wrap()
                    ->extraCellAttributes(['dir' => 'rtl']),
                TextColumn::make('preferred_time')
                    ->label('Time')
                    ->visibleFrom('lg'),
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => ServiceBooking::STATUS_OPTIONS[$state] ?? $state)
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'confirmed' => 'info',
                        'in_progress' => 'primary',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    })
                    ->visibleFrom('md'),
            ])
            ->emptyStateIcon(Heroicon::OutlinedWrenchScrewdriver)
            ->emptyStateHeading('No active service bookings')
            ->recordActions(ActionGroup::make([
                Action::make('openBooking')
                    ->label('Open booking')
                    ->icon(Heroicon::Eye)
                    ->url(fn (ServiceBooking $record): string => ServiceBookingResource::getUrl('view', ['record' => $record])),
                Action::make('assignToMe')
                    ->label('Assign to me')
                    ->icon(Heroicon::UserPlus)
                    ->color('gray')
                    ->visible(fn (ServiceBooking $record): bool => $record->user_id !== auth()->id())
                    ->action(fn (ServiceBooking $record) => $record->update(['user_id' => auth()->id()]))
                    ->successNotificationTitle('Booking assigned to you'),
                Action::make('confirm')
                    ->label('Confirm')
                    ->icon(Heroicon::CheckCircle)
                    ->color('info')
                    ->visible(fn (ServiceBooking $record): bool => $record->status === 'pending')
                    ->action(fn (ServiceBooking $record) => $record->update(['status' => 'confirmed']))
                    ->successNotificationTitle('Booking confirmed'),
                Action::make('startWork')
                    ->label('Start work')
                    ->icon(Heroicon::WrenchScrewdriver)
                    ->color('primary')
                    ->visible(fn (ServiceBooking $record): bool => in_array($record->status, ['pending', 'confirmed'], true))
                    ->action(fn (ServiceBooking $record) => $record->update(['status' => 'in_progress']))
                    ->successNotificationTitle('Booking moved to in progress'),
                Action::make('complete')
                    ->label('Complete')
                    ->icon(Heroicon::CheckBadge)
                    ->color('success')
                    ->visible(fn (ServiceBooking $record): bool => $record->status !== 'completed')
                    ->action(fn (ServiceBooking $record) => $record->update(['status' => 'completed']))
                    ->successNotificationTitle('Service booking completed'),
            ])
                ->label('Actions')
                ->icon(Heroicon::EllipsisHorizontal)
                ->iconButton()
                ->color('gray'))
            ->recordActionsColumnLabel('')
            ->recordUrl(fn (ServiceBooking $record): string => ServiceBookingResource::getUrl('view', ['record' => $record]));
    }
}
