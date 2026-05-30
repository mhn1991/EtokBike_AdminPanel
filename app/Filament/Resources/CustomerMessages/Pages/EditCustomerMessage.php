<?php

namespace App\Filament\Resources\CustomerMessages\Pages;

use App\Filament\Resources\CustomerMessages\CustomerMessageResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditCustomerMessage extends EditRecord
{
    protected static string $resource = CustomerMessageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
