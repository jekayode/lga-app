<?php

namespace App\Filament\Resources\PollingUnits\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class PollingUnitForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('ward_id')
                    ->relationship('ward', 'name')
                    ->required(),
                TextInput::make('name')
                    ->required(),
                TextInput::make('code'),
                TextInput::make('location'),
                Toggle::make('is_active')
                    ->required(),
            ]);
    }
}
