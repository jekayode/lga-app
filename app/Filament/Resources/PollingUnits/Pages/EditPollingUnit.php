<?php

namespace App\Filament\Resources\PollingUnits\Pages;

use App\Filament\Resources\PollingUnits\PollingUnitResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPollingUnit extends EditRecord
{
    protected static string $resource = PollingUnitResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
