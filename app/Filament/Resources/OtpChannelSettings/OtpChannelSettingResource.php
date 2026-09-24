<?php

namespace App\Filament\Resources\OtpChannelSettings;

use App\Filament\Resources\OtpChannelSettings\Pages\CreateOtpChannelSetting;
use App\Filament\Resources\OtpChannelSettings\Pages\EditOtpChannelSetting;
use App\Filament\Resources\OtpChannelSettings\Pages\ListOtpChannelSettings;
use App\Filament\Resources\OtpChannelSettings\Schemas\OtpChannelSettingForm;
use App\Filament\Resources\OtpChannelSettings\Tables\OtpChannelSettingsTable;
use App\Models\OtpChannelSetting;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class OtpChannelSettingResource extends Resource
{
    protected static ?string $model = OtpChannelSetting::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return OtpChannelSettingForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return OtpChannelSettingsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListOtpChannelSettings::route('/'),
            'create' => CreateOtpChannelSetting::route('/create'),
            'edit' => EditOtpChannelSetting::route('/{record}/edit'),
        ];
    }
}
