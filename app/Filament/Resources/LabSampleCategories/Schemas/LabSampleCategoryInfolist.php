<?php

namespace App\Filament\Resources\LabSampleCategories\Schemas;

use App\Enums\RecordStatusEnum;
use App\Models\LabSampleCategory;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\IconEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class LabSampleCategoryInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                // =======================
                // ΒΑΣΙΚΑ ΣΤΟΙΧΕΙΑ
                // =======================
                Section::make('Βασικά στοιχεία')
                    ->description('Κύρια πληροφορία και αναγνωριστικά πεδία της κατηγορίας.')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('name')
                                    ->label('Ονομασία')
                                    ->weight('bold')
                                    ->columnSpan(1),

                                TextEntry::make('code')
                                    ->label('Κωδικός')
                                    ->placeholder('-')
                                    ->alignCenter()
                                    ->columnSpan(1),
                            ]),
                        TextEntry::make('description')
                            ->label('Περιγραφή')
                            ->placeholder('— καμία περιγραφή —')
                            ->columnSpanFull(),
                    ])
                    ->compact()
                    ->columnSpanFull(),

                // =======================
                // ΣΧΕΣΕΙΣ & ΑΝΤΙΣΤΟΙΧΙΣΕΙΣ
                // =======================
                Section::make('Αντιστοιχίσεις')
                    ->description('Εργαστήριο και τύπος δείγματος με τα οποία συνδέεται η κατηγορία.')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('lab.name')
                                    ->label('Εργαστήριο')
                                    ->icon('heroicon-o-building-office')
                                    ->badge()
                                    ->color('info'),

                                TextEntry::make('sampleType.name')
                                    ->label('Τύπος δείγματος')
                                    ->icon('heroicon-o-beaker')
                                    ->badge()
                                    ->color('success'),
                            ]),
                    ])
                    ->columnSpanFull(),

                // =======================
                // ΟΙΚΟΝΟΜΙΚΑ & ΠΡΟΤΥΠΑ
                // =======================
                Section::make('Οικονομικά & Πρότυπα')
                    ->description('Πληροφορίες σχετικά με την τιμή, τη μεθοδολογία και τα πρότυπα που εφαρμόζονται.')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('default_price')
                                    ->label('Τιμή (€)')
                                    ->money('EUR', true)
                                    ->alignRight()
                                    ->placeholder('-'),

                                TextEntry::make('currency_code')
                                    ->label('Νόμισμα')
                                    ->placeholder('-'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('method_ref')
                                    ->label('Μέθοδος αναφοράς')
                                    ->placeholder('-'),

                                TextEntry::make('standard_ref')
                                    ->label('Πρότυπο / Νομοθεσία')
                                    ->placeholder('-'),
                            ]),
                    ])
                    ->columnSpanFull(),

                // =======================
                // ΚΑΤΑΣΤΑΣΗ
                // =======================
                Section::make('Κατάσταση')
                    ->description('Ενδεικτική κατάσταση και ενημέρωση εγγραφής.')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('status')
                                    ->label('Κατάσταση')
                                    ->badge()
                                    ->icon(fn (RecordStatusEnum $state) => match ($state) {
                                        RecordStatusEnum::Active => 'heroicon-o-check-circle',
                                        RecordStatusEnum::Inactive => 'heroicon-o-x-circle',
                                        default => null,
                                    })
                                    ->color(fn (RecordStatusEnum $state) => match ($state) {
                                        RecordStatusEnum::Active => 'success',
                                        RecordStatusEnum::Inactive => 'gray',
                                        default => 'secondary',
                                    }),

                                TextEntry::make('updated_at')
                                    ->label('Τελευταία ενημέρωση')
                                    ->dateTime('d/m/Y H:i')
                                    ->placeholder('-'),
                            ]),
                    ])
                    ->columnSpanFull(),

                // =======================
                // ΜΕΤΑΔΕΔΟΜΕΝΑ
                // =======================
                Section::make('Μεταδεδομένα')
                    ->description('Πληροφορίες δημιουργίας της εγγραφής.')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('createdBy.name')
                                    ->label('Δημιουργήθηκε από')
                                    ->placeholder('-'),

                                TextEntry::make('created_at')
                                    ->label('Ημερομηνία δημιουργίας')
                                    ->dateTime('d/m/Y H:i')
                                    ->placeholder('-'),
                            ]),
                    ])
                    ->collapsed(), // default collapsed για καθαρό UI
            ]);
    }
}
