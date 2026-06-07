<?php

namespace App\Filament\Resources\CustomerProfiles\Pages;

use App\Filament\Resources\CustomerProfiles\CustomerProfileResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCustomerProfile extends EditRecord
{
    protected static string $resource = CustomerProfileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
