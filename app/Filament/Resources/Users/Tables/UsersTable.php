<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('email')
                    ->label('Email address')
                    ->searchable(),
                TextColumn::make('email_verified_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('two_factor_confirmed_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('phone')
                    ->searchable(),
                TextColumn::make('role')
                    ->badge()
                    ->searchable(),
                TextColumn::make('referral_code')
                    ->searchable(),
                TextColumn::make('referred_by_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('profession.name')
                    ->searchable(),
                IconColumn::make('has_disability')
                    ->boolean(),
                TextColumn::make('disability_notes')
                    ->searchable(),
                TextColumn::make('localGovernment.name')
                    ->searchable(),
                TextColumn::make('ward.name')
                    ->searchable(),
                TextColumn::make('pollingUnit.name')
                    ->searchable(),
                TextColumn::make('phone_verified_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('otp_verified_at')
                    ->dateTime()
                    ->sortable(),
                IconColumn::make('must_setup_two_factor')
                    ->boolean(),
                IconColumn::make('is_active')
                    ->boolean(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
