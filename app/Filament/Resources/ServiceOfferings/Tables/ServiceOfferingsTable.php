<?php

namespace App\Filament\Resources\ServiceOfferings\Tables;

use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class ServiceOfferingsTable
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
                    ->description(fn ($record): string => $record->subtitle)
                    ->searchable(),
                TextColumn::make('price_label')
                    ->searchable(),
                ColorColumn::make('thumbnail_color')
                    ->label('Card'),
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
            ->emptyStateIcon(Heroicon::OutlinedWrenchScrewdriver)
            ->emptyStateHeading('No service offerings yet')
            ->emptyStateDescription('Create workshop services so customers can browse them in the app.')
            ->filters([
                SelectFilter::make('service_category_id')
                    ->label('Category')
                    ->relationship('category', 'label'),
                TernaryFilter::make('is_active')
                    ->label('Visible in app'),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([]);
    }
}
