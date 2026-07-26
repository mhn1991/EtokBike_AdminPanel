<?php

namespace App\Filament\Resources\Products\Tables;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Support\Admin\FilamentLocalization;
use App\Support\Inventory\InventoryManager;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

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
                    ->formatStateUsing(fn (?string $state, Product $record): string => $record->category?->pathLabel() ?? (string) $state)
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
                    ->formatStateUsing(fn (string $state): string => __(Product::AVAILABILITY_OPTIONS[$state] ?? $state))
                    ->color(fn (string $state): string => match ($state) {
                        'in_stock' => 'success',
                        'low_stock' => 'warning',
                        'orderable' => 'info',
                        'out_of_stock' => 'danger',
                        default => 'gray',
                    })
                    ->visibleFrom('md')
                    ->sortable(),
                TextColumn::make('variants_count')
                    ->label('Variants')
                    ->counts('variants')
                    ->badge()
                    ->formatStateUsing(fn (?int $state): string => number_format($state ?? 0))
                    ->visibleFrom('lg')
                    ->sortable(),
                TextColumn::make('price_value')
                    ->label('Price')
                    ->formatStateUsing(fn (?int $state): string => number_format($state ?? 0))
                    ->visibleFrom('lg')
                    ->sortable(),
                TextColumn::make('stock_quantity')
                    ->label('On hand')
                    ->badge()
                    ->formatStateUsing(fn (?int $state): string => number_format($state ?? 0))
                    ->color(fn (Product $record): string => match (true) {
                        $record->stock_quantity <= 0 => 'danger',
                        $record->minimum_stock > 0 && $record->stock_quantity <= $record->minimum_stock => 'warning',
                        default => 'success',
                    })
                    ->sortable(),
                TextColumn::make('warehouse_location')
                    ->label('Location')
                    ->placeholder(__('-'))
                    ->visibleFrom('xl')
                    ->searchable(),
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
            ->emptyStateHeading(__('No products yet'))
            ->emptyStateDescription(__('Create products to publish them into the mobile shop.'))
            ->filters([
                SelectFilter::make('product_category_id')
                    ->label('Category')
                    ->options(fn (): array => ProductCategory::formOptions()),
                SelectFilter::make('availability')
                    ->options(FilamentLocalization::options(Product::AVAILABILITY_OPTIONS)),
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
                Action::make('stockIn')
                    ->label('Add stock')
                    ->icon(Heroicon::PlusCircle)
                    ->color('success')
                    ->form([
                        TextInput::make('quantity')
                            ->required()
                            ->integer()
                            ->minValue(1)
                            ->default(1),
                        Textarea::make('reason')
                            ->rows(3)
                            ->placeholder(__('Supplier delivery, return, correction...'))
                            ->maxLength(255),
                    ])
                    ->action(fn (Product $record, array $data) => app(InventoryManager::class)->adjust(
                        product: $record,
                        quantityDelta: (int) $data['quantity'],
                        type: 'stock_in',
                        reason: $data['reason'] ?? null,
                        userId: auth()->id(),
                    ))
                    ->successNotificationTitle(__('Stock added')),
                Action::make('stockOut')
                    ->label('Remove stock')
                    ->icon(Heroicon::MinusCircle)
                    ->color('danger')
                    ->requiresConfirmation()
                    ->form([
                        TextInput::make('quantity')
                            ->required()
                            ->integer()
                            ->minValue(1)
                            ->default(1),
                        Textarea::make('reason')
                            ->rows(3)
                            ->placeholder(__('Damaged, lost, internal use, correction...'))
                            ->maxLength(255),
                    ])
                    ->action(fn (Product $record, array $data) => app(InventoryManager::class)->adjust(
                        product: $record,
                        quantityDelta: -((int) $data['quantity']),
                        type: 'manual_removal',
                        reason: $data['reason'] ?? null,
                        userId: auth()->id(),
                    ))
                    ->successNotificationTitle(__('Stock removed')),
                Action::make('feature')
                    ->label('Feature in app')
                    ->icon(Heroicon::Fire)
                    ->color('warning')
                    ->visible(fn (Product $record): bool => ! $record->is_featured)
                    ->action(fn (Product $record) => $record->update(['is_featured' => true]))
                    ->successNotificationTitle(__('Product marked featured')),
                Action::make('unfeature')
                    ->label('Remove featured')
                    ->icon(Heroicon::NoSymbol)
                    ->color('gray')
                    ->visible(fn (Product $record): bool => (bool) $record->is_featured)
                    ->action(fn (Product $record) => $record->update(['is_featured' => false]))
                    ->successNotificationTitle(__('Product removed from featured')),
                Action::make('hideFromApp')
                    ->label('Hide from app')
                    ->icon(Heroicon::EyeSlash)
                    ->color('danger')
                    ->visible(fn (Product $record): bool => (bool) $record->is_active)
                    ->requiresConfirmation()
                    ->action(fn (Product $record) => $record->update(['is_active' => false]))
                    ->successNotificationTitle(__('Product hidden from the app')),
                Action::make('showInApp')
                    ->label('Show in app')
                    ->icon(Heroicon::Eye)
                    ->color('success')
                    ->visible(fn (Product $record): bool => ! $record->is_active)
                    ->action(fn (Product $record) => $record->update(['is_active' => true]))
                    ->successNotificationTitle(__('Product visible in the app')),
                Action::make('duplicate')
                    ->label('Duplicate')
                    ->icon(Heroicon::OutlinedDocumentDuplicate)
                    ->color('gray')
                    ->action(fn (Product $record) => static::duplicateProduct($record))
                    ->successNotificationTitle(__('Product duplicated as a hidden draft. Review the price, stock, and images before publishing it.')),
            ])
                ->label('Actions')
                ->icon(Heroicon::EllipsisHorizontal)
                ->iconButton()
                ->color('gray'))
            ->recordActionsColumnLabel('')
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('bulkShowInApp')
                        ->label('Show in app')
                        ->icon(Heroicon::Eye)
                        ->color('success')
                        ->action(fn (Collection $records) => $records->each->update(['is_active' => true]))
                        ->deselectRecordsAfterCompletion()
                        ->successNotificationTitle(__('Selected products are now visible in the app')),
                    BulkAction::make('bulkHideFromApp')
                        ->label('Hide from app')
                        ->icon(Heroicon::EyeSlash)
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(fn (Collection $records) => $records->each->update(['is_active' => false]))
                        ->deselectRecordsAfterCompletion()
                        ->successNotificationTitle(__('Selected products are hidden from the app')),
                    BulkAction::make('bulkFeature')
                        ->label('Feature in app')
                        ->icon(Heroicon::Fire)
                        ->color('warning')
                        ->action(fn (Collection $records) => $records->each->update(['is_featured' => true]))
                        ->deselectRecordsAfterCompletion()
                        ->successNotificationTitle(__('Selected products marked featured')),
                    BulkAction::make('bulkUnfeature')
                        ->label('Remove featured')
                        ->icon(Heroicon::NoSymbol)
                        ->color('gray')
                        ->action(fn (Collection $records) => $records->each->update(['is_featured' => false]))
                        ->deselectRecordsAfterCompletion()
                        ->successNotificationTitle(__('Selected products removed from featured')),
                ])->label('Bulk actions'),
            ]);
    }

    private static function duplicateProduct(Product $record): Product
    {
        return DB::transaction(function () use ($record): Product {
            // 'variants_count' is not a real column: the table's ->counts('variants')
            // aggregate injects it as a runtime attribute on $record, and replicate()
            // would otherwise try to insert it.
            $copy = $record->replicate(['slug', 'sku', 'stock_quantity', 'reserved_quantity', 'variants_count']);
            $copy->slug = Product::suggestUniqueSlug($record->title);
            $copy->sku = null;
            $copy->stock_quantity = 0;
            $copy->reserved_quantity = 0;
            $copy->is_active = false;
            $copy->is_featured = false;
            $copy->save();

            foreach ($record->variants as $variant) {
                $variantCopy = $variant->replicate(['sku', 'stock_quantity']);
                $variantCopy->product_id = $copy->id;
                $variantCopy->sku = null;
                $variantCopy->stock_quantity = 0;
                $variantCopy->save();
            }

            return $copy;
        });
    }
}
