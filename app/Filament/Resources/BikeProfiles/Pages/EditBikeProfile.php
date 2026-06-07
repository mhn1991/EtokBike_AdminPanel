<?php

namespace App\Filament\Resources\BikeProfiles\Pages;

use App\Filament\Resources\BikeProfiles\BikeProfileResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditBikeProfile extends EditRecord
{
    protected static string $resource = BikeProfileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
