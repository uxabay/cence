<?php

namespace App\Filament\Resources\Contracts\Schemas;

use App\Models\Contract;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;

class ContractInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // ⚠️ 0. Προειδοποίηση (Πάντα Full Width)
                Section::make()
                    ->visible(fn (Contract $record) => $record->has_warning)
                    ->schema([
                        TextEntry::make('warning_message')
                            ->label('')
                            ->default('⚠️ Η σύμβαση πλησιάζει τα όρια εκτέλεσης (δειγμάτων ή ποσού).')
                            ->color('warning')
                            ->weight(FontWeight::SemiBold)
                            ->columnSpanFull(),
                    ])
                    ->collapsible(false)
                    ->columnSpanFull(),

                // Main Grid Container: 2:1 column layout
                Grid::make(3)
                    ->columnSpanFull()
                    ->schema([

                        // === LEFT COLUMN (2/3 width) - Identity, Financials, Content, and Attachments ===
                        Grid::make(1) // Single column container for sections
                            ->columnSpan(2)
                            ->schema([

                                // 1. 📄 Βασικά Στοιχεία Σύμβασης
                                Section::make('Βασικά Στοιχεία Σύμβασης')
                                    ->description('Αριθμός, τίτλος και πελάτης.')
                                    ->icon('heroicon-o-document-text')
                                    ->compact()
                                    ->schema([
                                        Grid::make(3)
                                            ->schema([
                                                TextEntry::make('contract_number')
                                                    ->label('Αριθμός Σύμβασης')
                                                    ->weight(FontWeight::Bold)
                                                    ->color('primary')
                                                    ->icon('heroicon-o-hashtag'),

                                                TextEntry::make('customer.name')
                                                    ->label('Πελάτης')
                                                    ->weight(FontWeight::Medium)
                                                    ->icon('heroicon-o-user-group')
                                                    ->columnSpan(2),
                                            ]),
                                        // Τίτλος σε πλήρες πλάτος
                                        TextEntry::make('title')
                                            ->label('Τίτλος')
                                            ->weight(FontWeight::SemiBold)
                                            ->columnSpanFull(),
                                    ]),

                                // 2. 💰 Οικονομικά Στοιχεία & Εκτέλεση
                                Section::make('Οικονομικά Στοιχεία & Εκτέλεση')
                                    ->description('Σύγκριση προβλεπόμενων έναντι εκτελεσμένων ποσών και δειγμάτων.')
                                    ->icon('heroicon-o-banknotes')
                                    ->compact()
                                    ->schema([
                                        // ΑΝΝΑΛΥΣΗ ΠΡΟΫΠΟΛΟΓΙΣΜΟΥ (ΕΝΗΜΕΡΩΘΗΚΕ Η ΜΟΡΦΟΠΟΙΗΣΗ ΝΟΜΙΣΜΑΤΟΣ)
                                        Fieldset::make('Ανάλυση Προϋπολογισμού')
                                            ->schema([
                                                TextEntry::make('forecasted_amount')
                                                    ->label('Προϋπολογισμός')
                                                    ->money('EUR', locale: 'el') // Χρήση money() για σωστή μορφοποίηση €
                                                    ->color('gray'),

                                                TextEntry::make('stats.actual_amount')
                                                    ->label('Εκτελεσμένο')
                                                    ->money('EUR', locale: 'el') // Χρήση money() για σωστή μορφοποίηση €
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

                                        Fieldset::make('Στοιχεία Δειγμάτων')
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
                                    ]),

                                // 3. 📝 Περιγραφή & Παρατηρήσεις
                                Section::make('Περιγραφή & Παρατηρήσεις')
                                    ->description('Πλήρης περιγραφή όρων και πρόσθετες σημειώσεις.')
                                    ->icon('heroicon-o-clipboard-document-list')
                                    ->compact()
                                    ->schema([
                                        TextEntry::make('description')
                                            ->label('Περιγραφή')
                                            ->prose()
                                            ->placeholder('Δεν έχει καταχωρηθεί περιγραφή.')
                                            ->columnSpanFull(),

                                        TextEntry::make('remarks')
                                            ->label('Παρατηρήσεις')
                                            ->placeholder('Δεν υπάρχουν πρόσθετες παρατηρήσεις.')
                                            ->columnSpanFull(),
                                    ])
                                    ->columns(1),

                                // 4. 📎 Συνημμένο Έγγραφο (Moved to Left Column - Bottom)
                                Section::make('Συνημμένο Έγγραφο')
                                    ->description('Πρόσβαση στο αρχείο της υπογεγραμμένης σύμβασης.')
                                    ->icon('heroicon-o-paper-clip')
                                    ->compact()
                                    ->schema([
                                        TextEntry::make('fileAttachment.original_name')
                                            ->label('Αρχείο Σύμβασης')
                                            ->placeholder('Δεν υπάρχει συνημμένο αρχείο.')
                                            ->copyable()
                                            ->url(fn (Contract $record) => $record->fileAttachment?->getUrl(), shouldOpenInNewTab: true)
                                            ->icon('heroicon-o-arrow-top-right-on-square'),
                                    ])
                                    ->columns(1),
                            ]),


                        // === RIGHT COLUMN (1/3 width) - Duration, Status, and Audit ===
                        Grid::make(1)
                            ->columnSpan(1)
                            ->schema([

                                // 5. 📅 Χρονική Διάρκεια (Πρώτο)
                                Section::make('Χρονική Διάρκεια')
                                    ->description('Ημερομηνίες έναρξης και λήξης ισχύος.')
                                    ->icon('heroicon-o-calendar-days')
                                    ->compact()
                                    ->schema([
                                        TextEntry::make('date_start')
                                            ->label('Ημερομηνία Έναρξης')
                                            ->date()
                                            ->placeholder('-'),

                                        TextEntry::make('date_end')
                                            ->label('Ημερομηνία Λήξης')
                                            ->date()
                                            ->placeholder('Δεν έχει οριστεί'),
                                    ])
                                    ->columns(1),

                                // 6. ⚙️ Διαχείριση & Κατάσταση (Δεύτερο)
                                Section::make('Διαχείριση & Κατάσταση')
                                    ->description('Τρέχουσα διαχειριστική κατάσταση.')
                                    ->icon('heroicon-o-briefcase')
                                    ->compact()
                                    ->schema([
                                        TextEntry::make('status')
                                            ->label('Κατάσταση')
                                            ->badge()
                                            ->color(fn ($state) => $state?->getColor()) // Χρήση της μεθόδου getColor() του Enum
                                            ->icon(fn ($state) => $state?->getIcon()), // Χρήση της μεθόδου getIcon() του Enum
                                    ])
                                    ->columns(1),

                                // 7. 🕒 Ιστορικό Καταγραφών (Audit)
                                Section::make('Ιστορικό Καταγραφών')
                                    ->description('Χρονοσφραγίδες δημιουργίας και ενημέρωσης.')
                                    ->icon('heroicon-o-clock')
                                    ->compact()
                                    ->schema([
                                        TextEntry::make('created_at')
                                            ->label('Δημιουργήθηκε')
                                            ->dateTime('d/m/Y H:i')
                                            ->icon('heroicon-o-calendar-days')
                                            ->placeholder('-'),

                                        TextEntry::make('updated_at')
                                            ->label('Τελευταία ενημέρωση')
                                            ->dateTime('d/m/Y H:i')
                                            ->icon('heroicon-o-arrow-path')
                                            ->placeholder('-'),
                                    ])
                                    ->columns(1),
                            ]),
                    ]),
            ]);
    }
}
