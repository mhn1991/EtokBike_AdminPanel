<?php

namespace App\Filament\Resources\MessageDepartments\Pages;

use App\Filament\Resources\MessageDepartments\MessageDepartmentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMessageDepartments extends ListRecords
{
    protected static string $resource = MessageDepartmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
