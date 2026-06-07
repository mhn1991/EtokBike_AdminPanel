<?php

namespace App\Filament\Resources\ProgramBookings\Tables;

use App\Models\ProgramBooking;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ProgramBookingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->paginated(false)
            ->striped()
            ->columns([
                TextColumn::make('program.title')
                    ->label('Program')
                    ->description(fn (ProgramBooking $record): ?string => $record->program?->date_label)
                    ->searchable()
                    ->sortable(),
                TextColumn::make('customer_name')
                    ->label('Customer')
                    ->description(fn (ProgramBooking $record): ?string => $record->customer_phone)
                    ->searchable(),
                TextColumn::make('customer_email')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('attendees')
                    ->formatStateUsing(fn (?int $state): string => number_format($state ?? 0))
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => ProgramBooking::STATUS_OPTIONS[$state] ?? $state)
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'confirmed' => 'info',
                        'cancelled' => 'danger',
                        'attended' => 'success',
                        'no_show' => 'gray',
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
            ->emptyStateIcon(Heroicon::OutlinedTicket)
            ->emptyStateHeading('No program bookings yet')
            ->emptyStateDescription('Customer event and ride bookings will appear here.')
            ->filters([
                SelectFilter::make('program_id')
                    ->label('Program')
                    ->relationship('program', 'title'),
                SelectFilter::make('status')
                    ->options(ProgramBooking::STATUS_OPTIONS),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([]);
    }
}
