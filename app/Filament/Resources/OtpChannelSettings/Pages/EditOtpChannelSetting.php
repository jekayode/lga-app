<?php

namespace App\Filament\Resources\OtpChannelSettings\Pages;

use App\Filament\Resources\OtpChannelSettings\OtpChannelSettingResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditOtpChannelSetting extends EditRecord
{
    protected static string $resource = OtpChannelSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
