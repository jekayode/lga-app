<?php

namespace App\Filament\Resources\AgentApplications;

use App\Filament\Resources\AgentApplications\Pages\CreateAgentApplication;
use App\Filament\Resources\AgentApplications\Pages\EditAgentApplication;
use App\Filament\Resources\AgentApplications\Pages\ListAgentApplications;
use App\Filament\Resources\AgentApplications\Schemas\AgentApplicationForm;
use App\Filament\Resources\AgentApplications\Tables\AgentApplicationsTable;
use App\Models\AgentApplication;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class AgentApplicationResource extends Resource
{
    protected static ?string $model = AgentApplication::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return AgentApplicationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AgentApplicationsTable::configure($table);
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
            'index' => ListAgentApplications::route('/'),
            'create' => CreateAgentApplication::route('/create'),
            'edit' => EditAgentApplication::route('/{record}/edit'),
        ];
    }
}
