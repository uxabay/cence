<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\IconEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Fieldset;
use Filament\Support\Enums\Alignment;
use Filament\Support\Enums\FontWeight;
use Filament\Schemas\Schema;

class UserInfo
{
    public static function configure(Schema $infolist): Schema
    {
        return $infolist->schema([
            Grid::make(2)->schema([

                Section::make()
                    ->schema([
                        // Προσωπικά στοιχεία
                        Fieldset::make('Προσωπικά στοιχεία')
                            ->schema([
                                TextEntry::make('name')
                                    ->label('Όνομα')
                                    ->icon('heroicon-m-user')
                                    ->weight(FontWeight::Medium),

                                TextEntry::make('email')
                                    ->label('Email')
                                    ->copyable()
                                    ->icon('heroicon-m-envelope'),

                                TextEntry::make('roles.name')
                                    ->label('Ρόλοι')
                                    ->badge()
                                    ->separator(', '),

                                TextEntry::make('status')
                                    ->label('Κατάσταση')
                                    ->badge(),
                            ])
                            ->columnSpan(1),

                        // Δραστηριότητα
                        Fieldset::make('Δραστηριότητα')
                            ->schema([
                                TextEntry::make('last_login_at')
                                    ->label('Τελευταία σύνδεση')
                                    ->dateTime('d/m/Y H:i')
                                    ->placeholder('Καμία'),

                                TextEntry::make('last_activity_at')
                                    ->label('Τελευταία δραστηριότητα')
                                    ->dateTime('d/m/Y H:i')
                                    ->placeholder('Καμία'),

                                IconEntry::make('force_password_reset')
                                    ->label('Υποχρεωτική αλλαγή κωδικού'),
                            ])
                            ->columnSpan(1),

                        Grid::make(1)->schema([
                            // Μεταδεδομένα
                            Fieldset::make('Μεταδεδομένα')
                                ->schema([
                                    TextEntry::make('createdBy.name')
                                        ->label('Δημιουργήθηκε από')
                                        ->placeholder('-'),

                                    TextEntry::make('updatedBy.name')
                                        ->label('Ενημερώθηκε από')
                                        ->placeholder('-'),

                                    TextEntry::make('created_at')
                                        ->label('Ημερομηνία δημιουργίας')
                                        ->dateTime('d/m/Y H:i'),

                                    TextEntry::make('updated_at')
                                        ->label('Τελευταία ενημέρωση')
                                        ->dateTime('d/m/Y H:i'),
                                ])
                        ])
                        ->columnSpan(2),

                    ])
                    ->columns(2)
                    ->columnSpan(2),
            ])
            ->columnSpan(2)

        ]);
    }
}
