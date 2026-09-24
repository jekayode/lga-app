<?php

namespace App\Filament\Resources\LocalGovernments\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class LocalGovernmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('code'),
                TextInput::make('state')
                    ->required()
                    ->default('Lagos'),
                Toggle::make('is_active')
                    ->required(),
            ]);
    }
}
