<?php

namespace App\Filament\Resources\Registrations\Schemas;

use App\Models\Participant;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class RegistrationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Registration Details')
                    ->schema([
                        Select::make('event_id')
                            ->relationship('event', 'title')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('participant_id')
                            ->relationship('participant', 'full_name')
                            ->getOptionLabelFromRecordUsing(fn (Participant $record): string => "{$record->full_name} ({$record->nokp})")
                            ->searchable()
                            ->preload()
                            ->required(),
                        DateTimePicker::make('registered_at')
                            ->required(),
                        Select::make('attendance_status')
                            ->options([
                                'registered' => 'Registered',
                                'attended' => 'Attended',
                                'no_show' => 'No-show',
                            ])
                            ->required(),
                        DateTimePicker::make('checked_in_at'),
                        DateTimePicker::make('completed_at'),
                        Select::make('source')
                            ->options([
                                'legacy_import' => 'Legacy Import',
                                'public_form' => 'Public Form',
                                'admin' => 'Admin',
                            ])
                            ->required(),
                        Textarea::make('remarks')
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull()
                    ->columns(2),
            ]);
    }
}
