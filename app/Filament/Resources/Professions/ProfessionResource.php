<?php

namespace App\Filament\Resources\Professions;

use App\Filament\Resources\Professions\Pages\CreateProfession;
use App\Filament\Resources\Professions\Pages\EditProfession;
use App\Filament\Resources\Professions\Pages\ListProfessions;
use App\Filament\Resources\Professions\Schemas\ProfessionForm;
use App\Filament\Resources\Professions\Tables\ProfessionsTable;
use App\Models\Profession;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ProfessionResource extends Resource
{
    protected static ?string $model = Profession::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return ProfessionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProfessionsTable::configure($table);
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
            'index' => ListProfessions::route('/'),
            'create' => CreateProfession::route('/create'),
            'edit' => EditProfession::route('/{record}/edit'),
        ];
    }
}
