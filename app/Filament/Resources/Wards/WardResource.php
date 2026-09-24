<?php

namespace App\Filament\Resources\Wards;

use App\Filament\Resources\Wards\Pages\CreateWard;
use App\Filament\Resources\Wards\Pages\EditWard;
use App\Filament\Resources\Wards\Pages\ListWards;
use App\Filament\Resources\Wards\Schemas\WardForm;
use App\Filament\Resources\Wards\Tables\WardsTable;
use App\Models\Ward;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class WardResource extends Resource
{
    protected static ?string $model = Ward::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return WardForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return WardsTable::configure($table);
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
            'index' => ListWards::route('/'),
            'create' => CreateWard::route('/create'),
            'edit' => EditWard::route('/{record}/edit'),
        ];
    }
}
