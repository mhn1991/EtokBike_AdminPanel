<?php

namespace App\Filament\Resources\CustomerProfiles\Pages;

use App\Filament\Resources\CustomerProfiles\CustomerProfileResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCustomerProfile extends CreateRecord
{
    protected static string $resource = CustomerProfileResource::class;
}
