<?php

namespace App\Filament\Resources\CustomerMessages\Pages;

use App\Filament\Resources\CustomerMessages\CustomerMessageResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewCustomerMessage extends ViewRecord
{
    protected static string $resource = CustomerMessageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
