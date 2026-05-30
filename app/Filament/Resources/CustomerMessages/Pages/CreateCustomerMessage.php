<?php

namespace App\Filament\Resources\CustomerMessages\Pages;

use App\Filament\Resources\CustomerMessages\CustomerMessageResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCustomerMessage extends CreateRecord
{
    protected static string $resource = CustomerMessageResource::class;
}
