<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->required(),
                DateTimePicker::make('email_verified_at'),
                DateTimePicker::make('last_login_at'),
                DateTimePicker::make('last_activity_at'),
                Toggle::make('force_password_reset')
                    ->required(),
                Select::make('status')
                    ->options(['active' => 'Active', 'suspended' => 'Suspended', 'archived' => 'Archived'])
                    ->default('active')
                    ->required(),
                TextInput::make('created_by')
                    ->numeric()
                    ->default(null),
                TextInput::make('updated_by')
                    ->numeric()
                    ->default(null),
                TextInput::make('password')
                    ->password()
                    ->required(),
            ]);
    }
}
