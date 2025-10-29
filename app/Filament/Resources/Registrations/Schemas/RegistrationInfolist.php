<?php

namespace App\Filament\Resources\Registrations\Schemas;

use App\Enums\RecordStatusEnum;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Fieldset;

class RegistrationInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                /*
                |--------------------------------------------------------------------------
                | ΒΑΣΙΚΑ ΣΤΟΙΧΕΙΑ ΠΡΩΤΟΚΟΛΛΟΥ
                |--------------------------------------------------------------------------
                */
                Section::make('Βασικά Στοιχεία')
                    ->icon('heroicon-o-document-text')
                    ->schema([
                        TextEntry::make('registration_number')
                            ->label('Αριθμός Πρωτοκόλλου')
                            ->icon('heroicon-o-document-text')
                            ->weight('medium')
                            ->color('primary'),

                        TextEntry::make('date')
                            ->label('Ημερομηνία')
                            ->date('d/m/Y')
                            ->icon('heroicon-o-calendar-days'),

                        TextEntry::make('year')
                            ->label('Έτος'),
                    ])
                    ->columnSpanFull()
                    ->columns(3),

                /*
                |--------------------------------------------------------------------------
                | ΔΕΙΓΜΑΤΑ ΕΡΓΑΣΤΗΡΙΟΥ
                |--------------------------------------------------------------------------
                */
                Section::make('Δείγματα Εργαστηρίου')
                    ->icon('heroicon-o-beaker')
                    ->schema([
                        TextEntry::make('labCategory.name')
                            ->label('Κατηγορία Δείγματος Εργαστηρίου')
                            ->badge()
                            ->color('info')
                            ->icon('heroicon-o-beaker'),

                        Fieldset::make('Σύνοψη Δειγμάτων')
                            ->schema([
                                TextEntry::make('num_samples_received')
                                    ->label('Ληφθέντα')
                                    ->numeric(),

                                TextEntry::make('not_valid_samples')
                                    ->label('Ακατάλληλα')
                                    ->numeric(),

                                TextEntry::make('total_samples')
                                    ->label('Έγκυρα')
                                    ->numeric()
                                    ->color('success')
                                    ->weight('medium'),
                            ])
                            ->columns(3),
                    ])
                    ->columnSpanFull()
                    ->columns(1),

                /*
                |--------------------------------------------------------------------------
                | ΠΕΛΑΤΗΣ & ΣΥΜΒΑΣΗ
                |--------------------------------------------------------------------------
                */
                Section::make('Πελάτης & Σύμβαση')
                    ->icon('heroicon-o-clipboard-document-check')
                    ->schema([
                        TextEntry::make('customer.name')
                            ->label('Πελάτης')
                            ->icon('heroicon-o-building-office')
                            ->weight('medium')
                            ->color('primary')
                            ->placeholder('-'),

                        TextEntry::make('contract.title')
                            ->label('Σύμβαση')
                            ->icon('heroicon-o-clipboard-document')
                            ->placeholder('-'),

                        TextEntry::make('contractSample.category.name')
                            ->label('Κατηγορία Δειγμάτων Σύμβασης')
                            ->icon('heroicon-o-rectangle-stack')
                            ->placeholder('-'),
                    ])
                    ->columnSpanFull()
                    ->columns(3),

                /*
                |--------------------------------------------------------------------------
                | ΠΑΡΑΤΗΡΗΣΕΙΣ & ΚΑΤΑΣΤΑΣΗ
                |--------------------------------------------------------------------------
                */
                Section::make('Κατάσταση & Παρατηρήσεις')
                    ->icon('heroicon-o-rectangle-stack')
                    ->schema([
                        TextEntry::make('status')
                            ->label('Κατάσταση')
                            ->badge()
                            ->color(fn ($state) => match ($state) {
                                RecordStatusEnum::Active->value => 'success',
                                RecordStatusEnum::Inactive->value => 'gray',
                                default => 'warning',
                            }),

                        TextEntry::make('comments')
                            ->label('Παρατηρήσεις')
                            ->placeholder('-')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                /*
                |--------------------------------------------------------------------------
                | ΜΕΤΑΔΕΔΟΜΕΝΑ
                |--------------------------------------------------------------------------
                */
                Section::make('Μεταδεδομένα')
                    ->collapsed()
                    ->collapsible()
                    ->schema([
                        TextEntry::make('created_at')
                            ->label('Δημιουργήθηκε')
                            ->dateTime('d/m/Y H:i')
                            ->icon('heroicon-o-clock'),

                        TextEntry::make('updated_at')
                            ->label('Τελευταία Ενημέρωση')
                            ->dateTime('d/m/Y H:i')
                            ->icon('heroicon-o-arrow-path'),

                        TextEntry::make('createdBy.name')
                            ->label('Καταχωρήθηκε από')
                            ->placeholder('-'),

                        TextEntry::make('updatedBy.name')
                            ->label('Τελευταία ενημέρωση από')
                            ->placeholder('-'),
                    ])
                    ->columns(2),
            ]);
    }
}
