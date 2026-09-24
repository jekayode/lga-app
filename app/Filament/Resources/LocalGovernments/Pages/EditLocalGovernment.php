<?php

namespace App\Filament\Resources\LocalGovernments\Pages;

use App\Filament\Resources\LocalGovernments\LocalGovernmentResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditLocalGovernment extends EditRecord
{
    protected static string $resource = LocalGovernmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
