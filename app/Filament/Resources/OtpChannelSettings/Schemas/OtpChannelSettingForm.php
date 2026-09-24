<?php

namespace App\Filament\Resources\OtpChannelSettings\Schemas;

use App\Enums\Role;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class OtpChannelSettingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('role')
                    ->options(Role::class)
                    ->required(),
                Toggle::make('sms_enabled')
                    ->required(),
                Toggle::make('email_enabled')
                    ->required(),
                Toggle::make('whatsapp_enabled')
                    ->required(),
            ]);
    }
}
