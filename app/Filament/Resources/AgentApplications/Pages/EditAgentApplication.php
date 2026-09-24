<?php

namespace App\Filament\Resources\AgentApplications\Pages;

use App\Filament\Resources\AgentApplications\AgentApplicationResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditAgentApplication extends EditRecord
{
    protected static string $resource = AgentApplicationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
