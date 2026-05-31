<?php

namespace App\Filament\Resources\Programs\Tables;

use App\Models\Program;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class ProgramsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->paginated(false)
            ->striped()
            ->columns([
                TextColumn::make('category.label')
                    ->label('Category')
                    ->sortable(),
                TextColumn::make('title')
                    ->description(fn (Program $record): string => $record->subtitle)
                    ->searchable()
                    ->sortable(),
                TextColumn::make('date_value')
                    ->date()
                    ->sortable(),
                TextColumn::make('program_state')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => Program::STATE_OPTIONS[$state] ?? $state)
                    ->color(fn (string $state): string => match ($state) {
                        'future' => 'success',
                        'finished' => 'gray',
                        'cancelled' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('status_label')
                    ->searchable(),
                ColorColumn::make('thumbnail_color')
                    ->label('Card'),
                TextColumn::make('capacity')
                    ->formatStateUsing(fn (?int $state): string => is_null($state) ? '-' : number_format($state))
                    ->sortable(),
                TextColumn::make('reserved_count')
                    ->formatStateUsing(fn (?int $state): string => number_format($state ?? 0))
                    ->sortable(),
                TextColumn::make('sort_order')
                    ->formatStateUsing(fn (?int $state): string => number_format($state ?? 0))
                    ->sortable(),
                IconColumn::make('is_active')
                    ->boolean(),
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
            ->emptyStateHeading('No programs yet')
            ->emptyStateDescription('Create rides, events, and programs to publish them in the app.')
            ->filters([
                SelectFilter::make('program_category_id')
                    ->label('Category')
                    ->relationship('category', 'label'),
                SelectFilter::make('program_state')
                    ->options(Program::STATE_OPTIONS),
                TernaryFilter::make('is_active')
                    ->label('Visible in app'),
            ])
            ->defaultSort('date_value')
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([]);
    }
}
