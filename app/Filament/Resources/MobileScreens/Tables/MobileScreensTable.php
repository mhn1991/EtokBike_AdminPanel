<?php

namespace App\Filament\Resources\MobileScreens\Tables;

use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class MobileScreensTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->paginated(false)
            ->striped()
            ->columns([
                TextColumn::make('screen_id')
                    ->label('Screen')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('title')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('sections_count')
                    ->counts('sections')
                    ->label('Sections')
                    ->sortable(),
                TextColumn::make('version')
                    ->formatStateUsing(fn (?int $state): string => number_format($state ?? 0))
                    ->sortable(),
                ToggleColumn::make('hide_title')
                    ->label('Hide title'),
                ToggleColumn::make('is_active')
                    ->label('API active'),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->emptyStateIcon(Heroicon::OutlinedDevicePhoneMobile)
            ->emptyStateHeading('No app pages yet')
            ->emptyStateDescription('Create app pages to override static mobile screen payloads.')
            ->filters([
                TernaryFilter::make('hide_title')
                    ->label('Hide title'),
                TernaryFilter::make('is_active')
                    ->label('API active'),
            ])
            ->defaultSort('screen_id')
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([]);
    }
}
