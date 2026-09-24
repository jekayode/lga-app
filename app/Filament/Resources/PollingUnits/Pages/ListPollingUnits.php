<?php

namespace App\Filament\Resources\PollingUnits\Pages;

use App\Filament\Resources\PollingUnits\PollingUnitResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPollingUnits extends ListRecords
{
    protected static string $resource = PollingUnitResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
