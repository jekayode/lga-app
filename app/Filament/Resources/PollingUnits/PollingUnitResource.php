<?php

namespace App\Filament\Resources\PollingUnits;

use App\Filament\Resources\PollingUnits\Pages\CreatePollingUnit;
use App\Filament\Resources\PollingUnits\Pages\EditPollingUnit;
use App\Filament\Resources\PollingUnits\Pages\ListPollingUnits;
use App\Filament\Resources\PollingUnits\Schemas\PollingUnitForm;
use App\Filament\Resources\PollingUnits\Tables\PollingUnitsTable;
use App\Models\PollingUnit;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PollingUnitResource extends Resource
{
    protected static ?string $model = PollingUnit::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return PollingUnitForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PollingUnitsTable::configure($table);
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
            'index' => ListPollingUnits::route('/'),
            'create' => CreatePollingUnit::route('/create'),
            'edit' => EditPollingUnit::route('/{record}/edit'),
        ];
    }
}
