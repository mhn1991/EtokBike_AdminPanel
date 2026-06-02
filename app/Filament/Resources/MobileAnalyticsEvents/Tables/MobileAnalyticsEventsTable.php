<?php

namespace App\Filament\Resources\MobileAnalyticsEvents\Tables;

use App\Models\MobileAnalyticsEvent;
use Filament\Actions\ViewAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class MobileAnalyticsEventsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->paginated(false)
            ->striped()
            ->columns([
                TextColumn::make('event_name')
                    ->label('Event')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => MobileAnalyticsEvent::eventLabel($state))
                    ->color(fn (?string $state): string => MobileAnalyticsEvent::eventColor($state))
                    ->searchable(),
                TextColumn::make('screen_id')
                    ->label('Screen')
                    ->badge()
                    ->placeholder('-')
                    ->searchable(),
                TextColumn::make('action')
                    ->placeholder('-')
                    ->searchable(),
                TextColumn::make('device_id')
                    ->label('Device')
                    ->limit(18)
                    ->searchable(),
                TextColumn::make('session_id')
                    ->label('Session')
                    ->limit(18)
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('platform')
                    ->badge()
                    ->placeholder('-')
                    ->searchable(),
                TextColumn::make('app_version')
                    ->label('App version')
                    ->placeholder('-')
                    ->searchable(),
                TextColumn::make('occurred_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('ip_address')
                    ->label('IP')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->emptyStateIcon(Heroicon::OutlinedChartBar)
            ->emptyStateHeading('No app analytics logs yet')
            ->emptyStateDescription('Telemetry events from the Android app will appear here.')
            ->filters([
                SelectFilter::make('event_name')
                    ->label('Event')
                    ->options(MobileAnalyticsEvent::EVENT_OPTIONS),
                SelectFilter::make('screen_id')
                    ->label('Screen')
                    ->options(fn (): array => MobileAnalyticsEvent::query()
                        ->whereNotNull('screen_id')
                        ->distinct()
                        ->orderBy('screen_id')
                        ->pluck('screen_id', 'screen_id')
                        ->all()),
                SelectFilter::make('platform')
                    ->options(fn (): array => MobileAnalyticsEvent::query()
                        ->whereNotNull('platform')
                        ->distinct()
                        ->orderBy('platform')
                        ->pluck('platform', 'platform')
                        ->all()),
            ])
            ->defaultSort('occurred_at', 'desc')
            ->recordActions([
                ViewAction::make(),
            ])
            ->toolbarActions([]);
    }
}
