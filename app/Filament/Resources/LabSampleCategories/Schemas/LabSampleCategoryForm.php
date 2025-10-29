<?php

namespace App\Filament\Resources\LabSampleCategories\Schemas;

use App\Enums\RecordStatusEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class LabSampleCategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                // =======================
                // ΒΑΣΙΚΑ ΣΤΟΙΧΕΙΑ
                // =======================
                Section::make('Βασικά στοιχεία')
                    ->description('Καταχώριση των κύριων στοιχείων της κατηγορίας δείγματος.')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('name')
                                    ->label('Ονομασία')
                                    ->required()
                                    ->maxLength(255)
                                    ->columnSpanFull()
                                    ->hint('Το πλήρες όνομα της κατηγορίας δείγματος όπως εμφανίζεται στις αναφορές.'),

                                TextInput::make('code')
                                    ->label('Κωδικός')
                                    ->maxLength(50)
                                    ->hint('Μοναδικός εσωτερικός κωδικός ή συντομογραφία.')
                                    ->placeholder('-'),

                                Textarea::make('description')
                                    ->label('Περιγραφή')
                                    ->rows(3)
                                    ->placeholder('Προαιρετικά, σύντομη περιγραφή του περιεχομένου ή του σκοπού.')
                                    ->columnSpanFull(),
                            ]),
                    ])
                    ->compact()
                    ->columnSpanFull(),

                // =======================
                // ΑΝΤΙΣΤΟΙΧΙΣΕΙΣ
                // =======================
                Section::make('Αντιστοιχίσεις')
                    ->description('Σύνδεση με το εργαστήριο και τον τύπο δείγματος.')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('lab_id')
                                    ->label('Εργαστήριο')
                                    ->relationship('lab', 'name')
                                    ->searchable()
                                    ->required()
                                    ->placeholder('Επιλέξτε εργαστήριο')
                                    ->hint('Το εργαστήριο στο οποίο ανήκει η κατηγορία.'),

                                Select::make('sample_type_id')
                                    ->label('Τύπος δείγματος')
                                    ->relationship('sampleType', 'name')
                                    ->searchable()
                                    ->required()
                                    ->placeholder('Επιλέξτε τύπο δείγματος')
                                    ->hint('Ο γενικός τύπος δείγματος που αντιστοιχεί στην κατηγορία.'),
                            ]),
                    ])
                    ->columnSpanFull(),

                // =======================
                // ΟΙΚΟΝΟΜΙΚΑ & ΠΡΟΤΥΠΑ
                // =======================
                Section::make('Οικονομικά & Πρότυπα')
                    ->description('Οικονομικά στοιχεία και επιστημονικές αναφορές.')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('default_price')
                                    ->label('Τιμή (€)')
                                    ->numeric()
                                    ->step(0.01)
                                    ->prefix('€')
                                    ->default(0.00)
                                    ->required()
                                    ->hint('Η προκαθορισμένη τιμή για την εξέταση αυτής της κατηγορίας.'),

                                TextInput::make('currency_code')
                                    ->label('Νόμισμα')
                                    ->maxLength(3)
                                    ->default('EUR')
                                    ->hint('Συνήθως EUR για ευρώ.'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('method_ref')
                                    ->label('Μέθοδος αναφοράς')
                                    ->maxLength(255)
                                    ->placeholder('-')
                                    ->hint('Αναφορά στη χρησιμοποιούμενη μέθοδο ή πρωτόκολλο.'),

                                TextInput::make('standard_ref')
                                    ->label('Πρότυπο / Νομοθεσία')
                                    ->maxLength(255)
                                    ->placeholder('-')
                                    ->hint('Πρότυπο, οδηγία ή κανονισμός που σχετίζεται με την εξέταση.'),
                            ]),
                    ])
                    ->columnSpanFull(),

                // =======================
                // ΚΑΤΑΣΤΑΣΗ
                // =======================
                Section::make('Κατάσταση')
                    ->description('Ενεργοποίηση ή απενεργοποίηση της κατηγορίας.')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('status')
                                    ->label('Κατάσταση')
                                    ->options(collect(RecordStatusEnum::cases())->mapWithKeys(
                                        fn($case) => [$case->value => $case->getLabel()]
                                    ))
                                    ->default(RecordStatusEnum::Active->value)
                                    ->required()
                                    ->hint('Μόνο οι ενεργές κατηγορίες είναι διαθέσιμες για επιλογή σε φόρμες και συμβάσεις.'),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
