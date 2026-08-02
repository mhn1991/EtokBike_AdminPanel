<?php

namespace App\Filament\Resources\Receipts;

use App\Filament\Resources\Receipts\Pages\CreateReceipt;
use App\Filament\Resources\Receipts\Pages\EditReceipt;
use App\Filament\Resources\Receipts\Pages\ListReceipts;
use App\Filament\Resources\Receipts\Pages\ViewReceipt;
use App\Filament\Resources\Receipts\RelationManagers\ItemsRelationManager;
use App\Filament\Resources\Receipts\Schemas\ReceiptForm;
use App\Filament\Resources\Receipts\Schemas\ReceiptInfolist;
use App\Models\Receipt;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ReceiptResource extends Resource
{
    protected static ?string $model = Receipt::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedReceiptPercent;

    protected static ?string $navigationLabel = 'رسیدها';

    protected static string|\UnitEnum|null $navigationGroup = 'مالی';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'رسید';

    protected static ?string $pluralModelLabel = 'رسیدها';

    protected static ?string $recordTitleAttribute = 'receipt_number';

    public static function form(Schema $schema): Schema
    {
        return ReceiptForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ReceiptInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->striped()
            ->columns([
                TextColumn::make('receipt_number')
                    ->label('Receipt')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('customer_name')
                    ->description(fn (Receipt $record): string => collect([
                        $record->customer_phone,
                        $record->order?->order_number,
                        $record->returnRequest?->return_number,
                    ])->filter()->join(' · '))
                    ->searchable()
                    ->wrap(),
                TextColumn::make('type')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => __(Receipt::TYPE_OPTIONS[$state] ?? $state))
                    ->color(fn (string $state): string => match ($state) {
                        'credit_note' => 'danger',
                        'invoice' => 'info',
                        default => 'success',
                    })
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => __(Receipt::STATUS_OPTIONS[$state] ?? $state))
                    ->color(fn (string $state): string => match ($state) {
                        'issued' => 'success',
                        'cancelled' => 'danger',
                        default => 'warning',
                    })
                    ->sortable(),
                TextColumn::make('total')
                    ->formatStateUsing(fn (?int $state): string => number_format($state ?? 0))
                    ->sortable(),
                TextColumn::make('payment_status')
                    ->label('Payment')
                    ->placeholder(__('-'))
                    ->visibleFrom('lg')
                    ->sortable(),
                TextColumn::make('issued_at')
                    ->dateTime()
                    ->placeholder(__('-'))
                    ->visibleFrom('xl')
                    ->sortable(),
            ])
            ->emptyStateIcon(Heroicon::OutlinedReceiptPercent)
            ->emptyStateHeading(__('No receipts yet'))
            ->emptyStateDescription(__('Generate receipts from orders, invoices, or credit notes for returns.'))
            ->filters([
                SelectFilter::make('type')
                    ->options(\App\Support\Admin\FilamentLocalization::options(Receipt::TYPE_OPTIONS)),
                SelectFilter::make('status')
                    ->options(\App\Support\Admin\FilamentLocalization::options(Receipt::STATUS_OPTIONS)),
            ])
            ->defaultSort('issued_at', 'desc')
            ->recordActions(ActionGroup::make([
                ViewAction::make()
                    ->label('Open details'),
                EditAction::make(),
                Action::make('print')
                    ->label('Print receipt')
                    ->icon(Heroicon::Printer)
                    ->color('gray')
                    ->url(fn (Receipt $record): string => route('admin.receipts.print', $record))
                    ->openUrlInNewTab(),
            ])
                ->label('Actions')
                ->icon(Heroicon::EllipsisHorizontal)
                ->iconButton()
                ->color('gray'))
            ->recordActionsColumnLabel('')
            ->toolbarActions([]);
    }

    public static function getRelations(): array
    {
        return [
            ItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListReceipts::route('/'),
            'create' => CreateReceipt::route('/create'),
            'view' => ViewReceipt::route('/{record}'),
            'edit' => EditReceipt::route('/{record}/edit'),
        ];
    }
}
