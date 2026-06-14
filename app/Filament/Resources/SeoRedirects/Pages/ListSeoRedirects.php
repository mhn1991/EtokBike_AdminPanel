<?php

namespace App\Filament\Resources\SeoRedirects\Pages;

use App\Filament\Resources\SeoRedirects\SeoRedirectResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSeoRedirects extends ListRecords
{
    protected static string $resource = SeoRedirectResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
