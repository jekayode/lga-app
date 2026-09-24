<?php

namespace App\Filament\Resources\Wards\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class WardForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('local_government_id')
                    ->relationship('localGovernment', 'name')
                    ->required(),
                TextInput::make('name')
                    ->required(),
                TextInput::make('code'),
                Toggle::make('is_active')
                    ->required(),
            ]);
    }
}
