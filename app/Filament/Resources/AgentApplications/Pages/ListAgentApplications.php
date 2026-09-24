<?php

namespace App\Filament\Resources\AgentApplications\Pages;

use App\Filament\Resources\AgentApplications\AgentApplicationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAgentApplications extends ListRecords
{
    protected static string $resource = AgentApplicationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
