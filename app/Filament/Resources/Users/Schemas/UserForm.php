<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                TextInput::make('password')
                    ->password()
                    ->label('Password')
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->rule('confirmed')
                    ->dehydrated(fn (?string $state): bool => filled($state))
                    ->autocomplete('new-password'),
                TextInput::make('password_confirmation')
                    ->label('Confirm password')
                    ->password()
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->dehydrated(false)
                    ->autocomplete('new-password'),
            ]);
    }
}
