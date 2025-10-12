<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use App\Enums\UserStatus;
use Spatie\Permission\Models\Role;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->schema([
            Grid::make(1)->schema([

                // Γενικά στοιχεία
                Grid::make(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Όνομα')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->unique(ignoreRecord: true)
                            ->required(),
                    ])->columnSpan(1),

                // Κατάσταση & ασφάλεια
                Fieldset::make('Κατάσταση & ασφάλεια')
                    ->schema([
                        Select::make('status')
                            ->label('Κατάσταση')
                            ->options(UserStatus::class)
                            ->default(UserStatus::ACTIVE)
                            ->required(),

                        Toggle::make('force_password_reset')
                            ->label('Υποχρεωτική αλλαγή κωδικού')
                            ->default(false),
                    ]),
            ])->columnSpan(2),

            Grid::make()
                ->schema([
                    // Ρόλοι & Δικαιώματα
                    Fieldset::make('Ρόλοι & Δικαιώματα')
                        ->schema([
                            Select::make('roles')
                                ->label('Ρόλοι')
                                ->multiple()
                                ->relationship('roles', 'name')
                                ->preload()
                                ->searchable()
                                ->placeholder('Επιλέξτε έναν ή περισσότερους ρόλους')
                                ->helperText('Καθορίστε τους ρόλους πρόσβασης του χρήστη.')
                                ->visible(fn ($livewire) => auth()->user()->can('manage_roles')),
                        ])->columns(1),

                    // Κωδικός πρόσβασης
                    Fieldset::make('Κωδικός πρόσβασης')
                        ->schema([
                            TextInput::make('password')
                                ->label('Κωδικός πρόσβασης')
                                ->password()
                                ->revealable()
                                // Μόνο αν έχει τιμή, κάνε dehydrate και bcrypt
                                ->dehydrateStateUsing(fn ($state) => filled($state) ? bcrypt($state) : null)
                                // ΜΗΝ το περιλαμβάνεις καθόλου στο update αν είναι κενό
                                ->dehydrated(fn ($state) => filled($state))
                                ->required(fn (string $context): bool => $context === 'create')
                                ->minLength(8)
                                ->maxLength(255)
                                ->helperText('Αφήστε κενό εάν δεν θέλετε να αλλάξει ο κωδικός.'),
                        ])->columns(1),

                ])->columnSpan(2)
        ]);
    }
}
