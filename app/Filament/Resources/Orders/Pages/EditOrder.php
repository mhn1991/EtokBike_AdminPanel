<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Filament\Resources\Orders\OrderResource;
use App\Filament\Resources\Receipts\ReceiptResource;
use App\Support\Receipts\ReceiptGenerator;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;

class EditOrder extends EditRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            Action::make('generateReceipt')
                ->label('Generate receipt')
                ->icon(Heroicon::DocumentCheck)
                ->action(function () {
                    $receipt = app(ReceiptGenerator::class)->forOrder($this->record, 'receipt');

                    return redirect(ReceiptResource::getUrl('view', ['record' => $receipt]));
                }),
            DeleteAction::make(),
        ];
    }
}
