<?php

namespace App\Filament\Resources\AgentApplications\Schemas;

use App\Enums\AgentApplicationStatus;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class AgentApplicationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required(),
                Select::make('status')
                    ->options(AgentApplicationStatus::class)
                    ->default('pending')
                    ->required(),
                Textarea::make('motivation')
                    ->columnSpanFull(),
                TextInput::make('reviewed_by')
                    ->numeric(),
                DateTimePicker::make('reviewed_at'),
                Textarea::make('review_notes')
                    ->columnSpanFull(),
            ]);
    }
}
