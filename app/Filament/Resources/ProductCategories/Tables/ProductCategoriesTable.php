<?php

namespace App\Filament\Resources\ProductCategories\Tables;

use App\Models\ProductCategory;
use Filament\Actions\EditAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class ProductCategoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->striped()
            ->columns([
                TextColumn::make('label')
                    ->label('Category')
                    ->formatStateUsing(fn (string $state, ProductCategory $record): string => str_repeat('— ', $record->breadcrumbCategories()->count() - 1).$state)
                    ->description(fn (ProductCategory $record): string => $record->parent ? __('Under :parent', ['parent' => $record->parent->pathLabel()]) : __('Top-level category'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('slug')
                    ->searchable()
                    ->visibleFrom('md'),
                TextColumn::make('parent.label')
                    ->label('Parent')
                    ->placeholder(__('-'))
                    ->visibleFrom('lg')
                    ->sortable(),
                TextColumn::make('products_count')
                    ->counts('products')
                    ->label('Direct products')
                    ->visibleFrom('md')
                    ->sortable(),
                TextColumn::make('children_count')
                    ->counts('children')
                    ->label('Subcategories')
                    ->visibleFrom('lg')
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
            ->emptyStateIcon(Heroicon::OutlinedTag)
            ->emptyStateHeading(__('No product categories yet'))
            ->emptyStateDescription(__('Categories organize the mobile shop filters and sections.'))
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
