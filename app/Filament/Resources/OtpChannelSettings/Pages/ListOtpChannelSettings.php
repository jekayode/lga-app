<?php

namespace App\Filament\Resources\OtpChannelSettings\Pages;

use App\Filament\Resources\OtpChannelSettings\OtpChannelSettingResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListOtpChannelSettings extends ListRecords
{
    protected static string $resource = OtpChannelSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
