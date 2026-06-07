<?php

namespace App\Filament\Resources\CustomerProfiles\Pages;

use App\Filament\Resources\CustomerProfiles\CustomerProfileResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCustomerProfiles extends ListRecords
{
    protected static string $resource = CustomerProfileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
