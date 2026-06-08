<?php

namespace App\Filament\Resources\Products\Tables;

use App\Models\Product;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->paginated(false)
            ->striped()
            ->columns([
                TextColumn::make('category.label')
                    ->label('Category')
                    ->visibleFrom('md')
                    ->sortable(),
                TextColumn::make('title')
                    ->description(fn (Product $record): string => collect([
                        $record->subtitle,
                        $record->price_value ? number_format($record->price_value) : null,
                        $record->stock_label,
                    ])->filter()->join(' · '))
                    ->searchable()
                    ->wrap()
                    ->extraCellAttributes(['dir' => 'rtl'])
                    ->sortable(),
                TextColumn::make('availability')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => Product::AVAILABILITY_OPTIONS[$state] ?? $state)
                    ->color(fn (string $state): string => match ($state) {
                        'in_stock' => 'success',
                        'low_stock' => 'warning',
                        'orderable' => 'info',
                        'out_of_stock' => 'danger',
                        default => 'gray',
                    })
                    ->visibleFrom('md')
                    ->sortable(),
                TextColumn::make('price_value')
                    ->label('Price')
                    ->formatStateUsing(fn (?int $state): string => number_format($state ?? 0))
                    ->visibleFrom('lg')
                    ->sortable(),
                TextColumn::make('stock_label')
                    ->visibleFrom('xl')
                    ->searchable(),
                ColorColumn::make('thumbnail_color')
                    ->label('Card')
                    ->visibleFrom('lg'),
                TextColumn::make('sort_order')
                    ->formatStateUsing(fn (?int $state): string => number_format($state ?? 0))
                    ->visibleFrom('xl')
                    ->sortable(),
                IconColumn::make('is_featured')
                    ->label('Featured')
                    ->visibleFrom('lg')
                    ->boolean(),
                IconColumn::make('is_active')
                    ->label('Visible')
                    ->visibleFrom('lg')
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
            ->emptyStateIcon(Heroicon::OutlinedShoppingBag)
            ->emptyStateHeading('No products yet')
            ->emptyStateDescription('Create products to publish them into the mobile shop.')
            ->filters([
                SelectFilter::make('product_category_id')
                    ->label('Category')
                    ->relationship('category', 'label'),
                SelectFilter::make('availability')
                    ->options(Product::AVAILABILITY_OPTIONS),
                TernaryFilter::make('is_featured')
                    ->label('Featured'),
                TernaryFilter::make('is_active')
                    ->label('Visible in app'),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->recordActions(ActionGroup::make([
                ViewAction::make()
                    ->label('Open details'),
                EditAction::make(),
                Action::make('feature')
                    ->label('Feature in app')
                    ->icon(Heroicon::Fire)
                    ->color('warning')
                    ->visible(fn (Product $record): bool => ! $record->is_featured)
                    ->action(fn (Product $record) => $record->update(['is_featured' => true]))
                    ->successNotificationTitle('Product marked featured'),
                Action::make('unfeature')
                    ->label('Remove featured')
                    ->icon(Heroicon::NoSymbol)
                    ->color('gray')
                    ->visible(fn (Product $record): bool => (bool) $record->is_featured)
                    ->action(fn (Product $record) => $record->update(['is_featured' => false]))
                    ->successNotificationTitle('Product removed from featured'),
                Action::make('hideFromApp')
                    ->label('Hide from app')
                    ->icon(Heroicon::EyeSlash)
                    ->color('danger')
                    ->visible(fn (Product $record): bool => (bool) $record->is_active)
                    ->requiresConfirmation()
                    ->action(fn (Product $record) => $record->update(['is_active' => false]))
                    ->successNotificationTitle('Product hidden from the app'),
                Action::make('showInApp')
                    ->label('Show in app')
                    ->icon(Heroicon::Eye)
                    ->color('success')
                    ->visible(fn (Product $record): bool => ! $record->is_active)
                    ->action(fn (Product $record) => $record->update(['is_active' => true]))
                    ->successNotificationTitle('Product visible in the app'),
            ])
                ->label('Actions')
                ->icon(Heroicon::EllipsisHorizontal)
                ->iconButton()
                ->color('gray'))
            ->recordActionsColumnLabel('')
            ->toolbarActions([]);
    }
}
