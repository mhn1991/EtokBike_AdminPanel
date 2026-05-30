<?php

namespace App\Filament\Resources\Programs\Tables;

use App\Models\Program;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ProgramsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->paginated(false)
            ->columns([
                TextColumn::make('category.label')
                    ->label('Category')
                    ->sortable(),
                TextColumn::make('title')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('subtitle')
                    ->searchable(),
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
            ->filters([
                SelectFilter::make('program_category_id')
                    ->label('Category')
                    ->relationship('category', 'label'),
                SelectFilter::make('program_state')
                    ->options(Program::STATE_OPTIONS),
            ])
            ->defaultSort('date_value')
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([]);
    }
}
