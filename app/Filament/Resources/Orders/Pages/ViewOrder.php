<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Filament\Resources\Orders\OrderResource;
use App\Filament\Resources\Receipts\ReceiptResource;
use App\Support\Receipts\ReceiptGenerator;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
            Action::make('generateReceipt')
                ->label('Generate receipt')
                ->icon(Heroicon::DocumentCheck)
                ->action(function () {
                    $receipt = app(ReceiptGenerator::class)->forOrder($this->record, 'receipt');

                    return redirect(ReceiptResource::getUrl('view', ['record' => $receipt]));
                }),
            Action::make('generateInvoice')
                ->label('Generate invoice')
                ->icon(Heroicon::DocumentCurrencyDollar)
                ->color('info')
                ->action(function () {
                    $receipt = app(ReceiptGenerator::class)->forOrder($this->record, 'invoice');

                    return redirect(ReceiptResource::getUrl('view', ['record' => $receipt]));
                }),
        ];
    }
}
