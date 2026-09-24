<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Enums\Role;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true),
                TextInput::make('phone')
                    ->tel()
                    ->required()
                    ->unique(ignoreRecord: true),
                TextInput::make('password')
                    ->password()
                    ->revealable()
                    ->dehydrated(fn (?string $state): bool => filled($state))
                    ->required(fn (string $operation): bool => $operation === 'create'),
                Select::make('role')
                    ->options(collect(Role::cases())->mapWithKeys(fn (Role $role) => [$role->value => $role->label()]))
                    ->required()
                    ->native(false),
                TextInput::make('referral_code')
                    ->disabled()
                    ->dehydrated(false),
                Select::make('profession_id')
                    ->relationship('profession', 'name')
                    ->searchable()
                    ->preload(),
                Select::make('local_government_id')
                    ->relationship('localGovernment', 'name')
                    ->searchable()
                    ->preload()
                    ->live(),
                Select::make('ward_id')
                    ->relationship(
                        'ward',
                        'name',
                        fn ($query, $get) => $get('local_government_id')
                            ? $query->where('local_government_id', $get('local_government_id'))
                            : $query
                    )
                    ->searchable()
                    ->preload()
                    ->live(),
                Select::make('polling_unit_id')
                    ->relationship(
                        'pollingUnit',
                        'name',
                        fn ($query, $get) => $get('ward_id')
                            ? $query->where('ward_id', $get('ward_id'))
                            : $query
                    )
                    ->searchable()
                    ->preload(),
                Toggle::make('has_disability'),
                TextInput::make('disability_notes'),
                Toggle::make('must_setup_two_factor')
                    ->label('Must complete 2FA setup'),
                Toggle::make('is_active')
                    ->default(true),
            ]);
    }
}
