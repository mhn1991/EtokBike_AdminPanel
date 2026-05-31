<?php

namespace App\Filament\Resources\ProgramCategories\Tables;

use Filament\Actions\EditAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class ProgramCategoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->paginated(false)
            ->striped()
            ->columns([
                TextColumn::make('slug')
                    ->searchable(),
                TextColumn::make('label')
                    ->searchable(),
                TextColumn::make('title')
                    ->searchable(),
                TextColumn::make('programs_count')
                    ->counts('programs')
                    ->label('Programs')
                    ->sortable(),
                TextColumn::make('sort_order')
                    ->formatStateUsing(fn (?int $state): string => number_format($state ?? 0))
                    ->sortable(),
                ToggleColumn::make('is_active')
                    ->label('Visible'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->emptyStateIcon(Heroicon::OutlinedCalendarDays)
            ->emptyStateHeading('No program categories yet')
            ->emptyStateDescription('Categories group rides and events in the mobile app.')
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Visible in app'),
            ])
            ->defaultSort('sort_order')
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([]);
    }
}
