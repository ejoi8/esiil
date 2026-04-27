<?php

namespace App\Filament\Resources\Registrations\Tables;

use App\Enums\CertificateType;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class RegistrationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('event.title')
                    ->label('Event')
                    ->wrap()
                    ->searchable(),
                TextColumn::make('participant.full_name')
                    ->searchable(),
                TextColumn::make('participant.nokp')
                    ->label('No. KP')
                    ->searchable(),
                TextColumn::make('registered_at')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
                TextColumn::make('attendance_status')
                    ->badge()
                    ->searchable(),
                TextColumn::make('checked_in_at')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
                TextColumn::make('completed_at')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
                TextColumn::make('source')
                    ->badge()
                    ->searchable(),
                TextColumn::make('certificate_type')
                    ->label('Certificate Type')
                    ->badge()
                    ->formatStateUsing(fn (mixed $state): string => filled($state) ? CertificateType::labelFor($state) : '-')
                    ->searchable(),
                TextColumn::make('cert_serial_number')
                    ->label('Certificate Serial')
                    ->searchable(),
                TextColumn::make('certificate_issued_at')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('registered_at', 'desc')
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
