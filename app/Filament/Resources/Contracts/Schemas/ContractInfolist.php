<?php

namespace App\Filament\Resources\Contracts\Schemas;

use App\Models\Contract;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;

class ContractInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // ⚠️ Προειδοποίηση
                Section::make()
                    ->visible(fn (Contract $record) => $record->has_warning)
                    ->schema([
                        TextEntry::make('warning_message')
                            ->label('')
                            ->default('⚠️ Η σύμβαση πλησιάζει τα όρια εκτέλεσης (δειγμάτων ή ποσού).')
                            ->color('warning')
                            ->weight('semibold')
                            ->columnSpanFull(),
                    ])
                    ->collapsible(false),

                // 📄 Βασικά Στοιχεία Σύμβασης
                Section::make('Βασικά Στοιχεία Σύμβασης')
                    ->icon('heroicon-o-document-text')
                    ->schema([
                        TextEntry::make('contract_number')
                            ->label('Αριθμός Σύμβασης')
                            ->weight('medium')
                            ->color('primary'),

                        TextEntry::make('title')
                            ->label('Τίτλος')
                            ->columnSpanFull(),

                        TextEntry::make('customer.name')
                            ->label('Πελάτης'),

                        TextEntry::make('status')
                            ->label('Κατάσταση')
                            ->badge()
                            ->color(fn (Contract $record) => match ($record->status->value ?? null) {
                                'active' => 'success',
                                'inactive' => 'gray',
                                default => 'secondary',
                            }),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),

                // 📅 Χρονική Διάρκεια & Συνημμένο
                Grid::make(2)
                    ->schema([
                        // Χρονική Διάρκεια
                        Section::make('Χρονική Διάρκεια')
                            ->icon('heroicon-o-calendar-days')
                            ->schema([
                                TextEntry::make('date_start')
                                    ->label('Ημερομηνία Έναρξης')
                                    ->date()
                                    ->placeholder('-'),

                                TextEntry::make('date_end')
                                    ->label('Ημερομηνία Λήξης')
                                    ->date()
                                    ->placeholder('-'),
                            ])
                            ->columns(2),

                        // Συνημμένο
                        Section::make('Συνημμένο Έγγραφο')
                            ->icon('heroicon-o-paper-clip')
                            ->schema([
                                TextEntry::make('fileAttachment.original_name')
                                    ->label('Αρχείο Σύμβασης')
                                    ->placeholder('-')
                                    ->copyable()
                                    ->url(fn (Contract $record) => $record->fileAttachment?->getUrl(), shouldOpenInNewTab: true)
                                    ->icon('heroicon-o-arrow-top-right-on-square'),
                            ]),
                    ])
                    ->columnSpanFull(),

                // 💰 Οικονομικά & Πρόοδος
                Section::make('Οικονομικά & Πρόοδος Υλοποίησης')
                    ->icon('heroicon-o-banknotes')
                    ->schema([
                        Fieldset::make('Συνολικά Στοιχεία')
                            ->schema([
                                TextEntry::make('forecasted_amount')
                                    ->label('Προϋπολογισμός (€)')
                                    ->numeric(decimalPlaces: 2)
                                    ->color('gray'),

                                TextEntry::make('stats.actual_amount')
                                    ->label('Εκτελεσμένο (€)')
                                    ->numeric(decimalPlaces: 2)
                                    ->color('success'),

                                TextEntry::make('progress_percentage')
                                    ->label('Ποσοστό Υλοποίησης')
                                    ->suffix('%')
                                    ->badge()
                                    ->color(fn ($state) => match (true) {
                                        $state >= 100 => 'danger',
                                        $state >= 90 => 'warning',
                                        $state > 0 => 'success',
                                        default => 'gray',
                                    }),
                            ])
                            ->columns(3),

                        Fieldset::make('Δείγματα')
                            ->schema([
                                TextEntry::make('stats.forecasted_samples')
                                    ->label('Προβλεπόμενα Δείγματα')
                                    ->numeric()
                                    ->color('gray'),

                                TextEntry::make('stats.actual_samples')
                                    ->label('Εκτελεσμένα Δείγματα')
                                    ->numeric()
                                    ->color('success'),

                                TextEntry::make('stats.remaining_samples')
                                    ->label('Υπόλοιπο')
                                    ->numeric()
                                    ->color('secondary')
                                    ->default(fn (Contract $record) =>
                                        max(0, ($record->stats['forecasted_samples'] ?? 0)
                                            - ($record->stats['actual_samples'] ?? 0))
                                    ),
                            ])
                            ->columns(3),
                    ])
                    ->columnSpanFull(),

                // 📝 Περιγραφή & Παρατηρήσεις
                Section::make('Περιγραφή & Παρατηρήσεις')
                    ->icon('heroicon-o-clipboard-document-list')
                    ->schema([
                        TextEntry::make('description')
                            ->label('Περιγραφή')
                            ->prose()
                            ->placeholder('-')
                            ->columnSpanFull(),

                        TextEntry::make('remarks')
                            ->label('Παρατηρήσεις')
                            ->placeholder('-')
                            ->columnSpanFull(),
                    ])
                    ->columns(1)
                    ->columnSpanFull(),
            ]);
    }
}
