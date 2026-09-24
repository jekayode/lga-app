<?php

namespace App\Filament\Resources\LocalGovernments;

use App\Filament\Resources\LocalGovernments\Pages\CreateLocalGovernment;
use App\Filament\Resources\LocalGovernments\Pages\EditLocalGovernment;
use App\Filament\Resources\LocalGovernments\Pages\ListLocalGovernments;
use App\Filament\Resources\LocalGovernments\Schemas\LocalGovernmentForm;
use App\Filament\Resources\LocalGovernments\Tables\LocalGovernmentsTable;
use App\Models\LocalGovernment;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class LocalGovernmentResource extends Resource
{
    protected static ?string $model = LocalGovernment::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return LocalGovernmentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LocalGovernmentsTable::configure($table);
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
            'index' => ListLocalGovernments::route('/'),
            'create' => CreateLocalGovernment::route('/create'),
            'edit' => EditLocalGovernment::route('/{record}/edit'),
        ];
    }
}
