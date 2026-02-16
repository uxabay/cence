<?php

namespace App\Filament\Resources\Registrations\Schemas;

use App\Enums\RecordStatusEnum;
use App\Models\ContractSample;
use App\Models\Registration;
use App\Models\RegistrationAnalysis;
use App\Models\Contract;
use Illuminate\Support\HtmlString;
use Filament\Actions;
use Filament\Actions\ActionGroup;
use Filament\Actions\Action;
use Filament\Infolists\Components\TextEntry;
use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Flex;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Repeater\TableColumn;
use Filament\Support\Enums\Alignment;
use App\Enums\CostCalculationTypeEnum;
use Filament\Schemas\Schema;
use Illuminate\Support\Carbon;
use Filament\Notifications\Notification;


class RegistrationForm
{
    /**
     * Ορίζει το σχήμα της φόρμας για το Πρωτόκολλο Καταχώρησης με 2/1 διάταξη.
     * Επιστρέφει ένα αντικείμενο Schema.
     *
     * @return \Filament\Schemas\Schema
     */
    public static function configure(Schema $schema): Schema
    {

        return $schema
            ->components([

                // ΕΞΩΤΕΡΙΚΟ GRID (2/1 DESIGN)
                Grid::make(3)
                    ->columnSpanFull()
                    ->schema([

                        // =======================================================
                        // 1. ΑΡΙΣΤΕΡΗ ΣΤΗΛΗ (2/3 - Κύρια Ροή Εργασίας)
                        // =======================================================
                        Grid::make(1) // Εσωτερικό Grid για στοίχιση των Sections
                            ->columnSpan(2)
                            ->schema([

                                /*
                                |--------------------------------------------------------------------------
                                | ΒΑΣΙΚΑ ΣΤΟΙΧΕΙΑ ΠΡΩΤΟΚΟΛΛΟΥ & ΠΕΛΑΤΗ (S1)
                                |--------------------------------------------------------------------------
                                */
                                // ➡️ ΑΛΛΑΓΗ 3: Αλλαγή τίτλου Section
                                Section::make('Βασικά Στοιχεία & Πελάτης')
                                    ->icon('heroicon-o-document-text')
                                    ->columns(3)
                                    ->compact()
                                    ->schema([

                                        Hidden::make('registration_number_manual')
                                            ->default(false)
                                            ->dehydrated(false),

                                        // Suppress flag: όταν το σύστημα κάνει set στο registration_number, να μην θεωρείται manual change
                                        Hidden::make('registration_number_system_set')
                                            ->default(false)
                                            ->dehydrated(false),

                                        DatePicker::make('date')
                                            ->label('Ημερομηνία')
                                            ->required()
                                            ->default(today())
                                            ->displayFormat('d/m/Y')
                                            ->native(false)
                                            ->closeOnDateSelection()
                                            ->live()
                                            ->afterStateHydrated(function ($state, callable $get, callable $set) {
                                                // Φόρτωσε year από date
                                                if (! filled($state)) {
                                                    $state = today();
                                                }

                                                $year = Carbon::parse($state)->year;
                                                $set('year', $year);

                                                // Σε create context (id κενό), ξαναπρότεινε πάντα next number
                                                // εκτός αν υπάρχει manual override από τον χρήστη.
                                                $isCreateContext = blank($get('id'));
                                                $shouldAutoSuggest = ! $get('registration_number_manual')
                                                    && ($isCreateContext || blank($get('registration_number')));

                                                if ($shouldAutoSuggest) {
                                                    $set('registration_number_system_set', true);
                                                    $set('registration_number', Registration::nextNumberForYear($year));
                                                    $set('registration_number_system_set', false);
                                                }
                                            })
                                            ->afterStateUpdated(function ($state, callable $get, callable $set)  {
                                                if (! filled($state)) {
                                                    return;
                                                }

                                                $year = Carbon::parse($state)->year;
                                                $set('year', $year);

                                                // ✅ Auto-update όταν αλλάζει ημερομηνία, ΜΟΝΟ αν δεν είναι manual
                                                if ($get('registration_number_manual')) {
                                                    return;
                                                }

                                                $set('registration_number_system_set', true);
                                                $set('registration_number', Registration::nextNumberForYear($year));
                                                $set('registration_number_system_set', false);
                                            }),

                                        /**
                                         * registration_number:
                                         * - ο χρήστης γράφει ελεύθερα
                                         * - στο blur γίνεται format σε 00001/2026 (ή με year που έδωσε)
                                         */
                                        TextInput::make('registration_number')
                                            ->label('Αριθμός Πρωτοκόλλου')
                                            ->required()
                                            ->maxLength(20)
                                            ->placeholder('π.χ. 00024/2025')
                                            ->columnSpan(1)
                                            ->suffixAction(
                                                Action::make('fetchNextRegistrationNumber')
                                                    ->icon('heroicon-o-arrow-path')
                                                    ->tooltip('Εύρεση επόμενου αριθμού πρωτοκόλλου για την ημερομηνία')
                                                    ->action(function (callable $get, callable $set) {
                                                        $date = $get('date') ?: today();
                                                        $year = Carbon::parse($date)->year;

                                                        $set('year', $year);
                                                        $set('registration_number_manual', false);
                                                        $set('registration_number_system_set', true);
                                                        $set('registration_number', Registration::nextNumberForYear($year));
                                                        $set('registration_number_system_set', false);

                                                        Notification::make()
                                                            ->title('Ο αριθμός πρωτοκόλλου ενημερώθηκε')
                                                            ->body("Ορίστηκε ο επόμενος διαθέσιμος αριθμός για το {$year}.")
                                                            ->success()
                                                            ->send();
                                                    })
                                            )
                                            ->live(onBlur: true) // ✅ format μόνο όταν φύγει από το πεδίο
                                            ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                                // Αν έγινε set από σύστημα (auto-suggest), ΜΗΝ το θεωρείς manual και μην ξανα-formatάρεις
                                                if ($get('registration_number_system_set')) {
                                                    return;
                                                }

                                                if (blank($state)) {
                                                    return;
                                                }

                                                $year = (int) ($get('year') ?? now()->year);
                                                $value = trim((string) $state);

                                                // Δέχεται: "1" ή "1/2025" ή "00001/2025"
                                                if (preg_match('/^(\d{1,})(?:\/(\d{4}))?$/', $value, $m)) {
                                                    $num = str_pad((int) $m[1], 5, '0', STR_PAD_LEFT);
                                                    $inputYear = isset($m[2]) ? (int) $m[2] : $year;

                                                    $formatted = sprintf('%s/%s', $num, $inputYear);

                                                    // Κλειδώνουμε manual από τη στιγμή που ο χρήστης το πείραξε
                                                    $set('registration_number_manual', true);

                                                    if ($formatted !== $value) {
                                                        $set('registration_number', $formatted);
                                                    }

                                                    return;
                                                }

                                                // Αν γράψει κάτι εκτός pattern (π.χ. "Α-12"), απλά το αφήνουμε,
                                                // αλλά κλειδώνουμε manual ώστε να μην το πατήσει το auto.
                                                $set('registration_number_manual', true);
                                            }),

                                        TextInput::make('year')
                                            ->label('Έτος')
                                            ->numeric()
                                            ->readOnly()
                                            ->dehydrated()
                                            ->default(today()->year) // ✅ αρχική τιμή
                                            ->suffixIcon('heroicon-o-calendar')
                                            ->columnSpan(1),

                                        // ➡️ ΑΛΛΑΓΗ 1: Το πεδίο customer_id μεταφέρθηκε εδώ από το Section S3
                                        // 🟦 Πελάτης
                                        Select::make('customer_id')
                                            ->label('Πελάτης')
                                            ->relationship('customer', 'name')
                                            ->searchable()
                                            ->preload()
                                            ->required()
                                            ->placeholder('Επιλέξτε πελάτη...')
                                            ->reactive()
                                            ->columnSpan(2), // ➡️ ΑΛΛΑΓΗ 1: Καταλαμβάνει 2 στήλες
                                    ]),


                                /*
                                |--------------------------------------------------------------------------
                                | ΔΕΙΓΜΑΤΑ ΕΡΓΑΣΤΗΡΙΟΥ (S2)
                                |--------------------------------------------------------------------------
                                */
                                Section::make('Δείγματα Εργαστηρίου')
                                    ->icon('heroicon-o-beaker')
                                    ->columns(3)
                                    ->compact()
                                    ->schema([

                                        Grid::make(2)
                                            ->schema([
                                                Select::make('lab_sample_category_id')
                                                    ->label('Κατηγορία Δείγματος Εργαστηρίου')
                                                    ->relationship('labCategory', 'name')
                                                    ->required()
                                                    ->preload()
                                                    ->searchable()
                                                    ->columnSpan(2)
                                                    ->placeholder('Επιλέξτε κατηγορία δειγμάτων...'),
                                            ])
                                            ->columnSpanFull(),

                                        TextInput::make('num_samples_received')
                                            ->label('Ληφθέντα Δείγματα')
                                            ->numeric()
                                            ->required()
                                            ->default(0)
                                            ->minValue(0)
                                            ->live()
                                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                                $notValid = (int) $get('not_valid_samples') ?? 0;

                                                if ($state < $notValid) {
                                                    $set('not_valid_samples', $state);
                                                }

                                                $set('total_samples', max(0, $state - $notValid));
                                            }),

                                        TextInput::make('not_valid_samples')
                                            ->label('Ακατάλληλα Δείγματα')
                                            ->numeric()
                                            ->default(0)
                                            ->minValue(0)
                                            ->live()
                                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                                $received = (int) $get('num_samples_received') ?? 0;

                                                if ($state > $received) {
                                                    $state = $received;
                                                    $set('not_valid_samples', $state);
                                                }

                                                $set('total_samples', max(0, $received - $state));
                                            })
                                            ->helperText('Τα ακατάλληλα δεν μπορεί να υπερβαίνουν τα ληφθέντα.'),

                                        TextInput::make('total_samples')
                                            ->label('Έγκυρα Δείγματα')
                                            ->numeric()
                                            ->readOnly()
                                            ->dehydrated()
                                            ->suffixIcon('heroicon-o-check-badge')
                                            ->extraAttributes(['class' => 'bg-gray-50 text-gray-700']),
                                    ]),


                                /*
                                |--------------------------------------------------------------------------
                                | ΑΝΑΛΥΣΕΙΣ (S4) - ΜΕΓΑΛΥΤΕΡΟ SECTION
                                |--------------------------------------------------------------------------
                                */
                                Section::make('Αναλύσεις')
                                    ->description('Προβολή των αναλύσεων που εκτελέστηκαν για το συγκεκριμένο πρωτόκολλο.')
                                    ->compact()
                                    ->schema([

                                        // -----------------------------
                                        // Επιλογή Πακέτου Αναλύσεων
                                        // -----------------------------
                                        Flex::make([
                                            Select::make('analysis_package_id')
                                                ->label('Πακέτο αναλύσεων')
                                                ->hidden(function (callable $get) {
                                                    $sampleId = $get('contract_sample_id');
                                                    if (! $sampleId) {
                                                        return true;
                                                    }

                                                    $sample = ContractSample::find($sampleId);

                                                    return $sample?->cost_calculation_type === CostCalculationTypeEnum::VARIABLE_COUNT;
                                                })

                                                ->options(fn ($get) =>
                                                    \App\Models\LabAnalysisPackage::query()
                                                        ->where('lab_sample_category_id', $get('lab_sample_category_id'))
                                                        ->active()
                                                        ->pluck('name', 'id')
                                                )
                                                ->searchable()
                                                ->placeholder('Επιλογή πακέτου…')
                                                ->native(false)
                                                ->columns(1),

                                            Action::make('addPackageAnalyses')
                                                ->label('Προσθήκη Πακέτου Αναλύσεων')
                                                ->color('primary')
                                                ->icon('heroicon-o-plus')
                                                ->action(function (array $data, callable $get, callable $set) {

                                                    $packageId = $get('analysis_package_id');
                                                    if (! $packageId) return;

                                                    $package = \App\Models\LabAnalysisPackage::with('analyses')->find($packageId);
                                                    if (! $package) return;

                                                    // Αν είμαστε σε edit existing registration, το id συνήθως υπάρχει στο state
                                                    $registrationId = $get('id'); // αν δεν παίζει στο δικό σου context, θα το πιάσουμε στο Save (Fix B)

                                                    $current = collect($get('analyses') ?? [])
                                                        ->filter(fn ($row) => filled($row['lab_analysis_id'] ?? null))
                                                        ->keyBy('lab_analysis_id');

                                                    $newRows = $package->analyses->map(function ($a) use ($registrationId) {
                                                        $row = [
                                                            'lab_analysis_id' => $a->id,
                                                            'analysis_name'   => $a->pivot->analysis_name,
                                                            'analysis_price'  => $a->pivot->analysis_price,
                                                        ];

                                                        // DB-aware: αν υπάρχει trashed row, βάλε το id για να γίνει update αντί για create
                                                        if ($registrationId) {
                                                            $existing = RegistrationAnalysis::withTrashed()
                                                                ->where('registration_id', $registrationId)
                                                                ->where('lab_analysis_id', $a->id)
                                                                ->first();

                                                            if ($existing) {
                                                                $row['id'] = $existing->id;
                                                            }
                                                        }

                                                        return $row;
                                                    })->keyBy('lab_analysis_id');

                                                    $merged = $current->merge($newRows)->values()->toArray();

                                                    $set('analyses', []);
                                                    $set('analyses', $merged);
                                                }),
                                        ])
                                        ->verticallyAlignEnd()
                                        ->columnSpanFull(),  // Flex takes full width of its parent (2/3)

                                        // -----------------------------
                                        // Repeater για τις αναλύσεις
                                        // -----------------------------
                                        Repeater::make('analyses')
                                            ->label('Λίστα Αναλύσεων')
                                            ->addActionLabel('Προσθήκη Ανάλυσης')
                                            ->defaultItems(0)
                                            ->dehydrated(true)   // ← ΚΡΙΣΙΜΟ
                                            ->live()             // ← εξασφαλίζει ότι το state ξαναγράφεται
                                            ->compact()
                                            ->hidden(function (callable $get) {
                                                $sampleId = $get('contract_sample_id');
                                                if (! $sampleId) {
                                                    return true;
                                                }

                                                $sample = ContractSample::find($sampleId);

                                                return $sample?->cost_calculation_type === CostCalculationTypeEnum::VARIABLE_COUNT;
                                            })
                                            ->table([
                                                TableColumn::make('Ανάλυση')->alignLeft(),
                                                TableColumn::make('Ονομασία Ανάλυσης')->alignLeft(),
                                                TableColumn::make('Τιμή (€)')->alignCenter(),
                                            ])
                                            ->schema([

                                                Select::make('lab_analysis_id')
                                                    ->label('Ανάλυση')
                                                    ->options(fn ($get) =>
                                                        \App\Models\LabAnalysis::query()
                                                            ->where('lab_sample_category_id', $get('../../lab_sample_category_id'))
                                                            ->active()
                                                            ->pluck('name', 'id')
                                                    )
                                                    ->reactive()
                                                    ->afterStateUpdated(function ($state, callable $set) {
                                                        if ($state) {
                                                            $a = \App\Models\LabAnalysis::find($state);
                                                            if ($a) {
                                                                $set('analysis_name', $a->name);
                                                                $set('analysis_price', $a->unit_price);
                                                            }
                                                        }
                                                    })
                                                    ->searchable()
                                                    ->required()
                                                    ->native(false),


                                                TextInput::make('analysis_name')
                                                    ->label('Ονομασία Ανάλυσης')
                                                    ->readOnly()
                                                    ->required(),

                                                TextInput::make('analysis_price')
                                                    ->label('Τιμή (€)')
                                                    ->numeric()
                                                    ->required(),
                                            ])
                                            ->columns(3)
                                            ->columnSpanFull(),

                                        TextInput::make('analyses_count')
                                            ->label('Συνολικός αριθμός αναλύσεων')
                                            ->numeric()
                                            ->minValue(0)
                                            ->default(0)
                                            ->required()
                                            ->visible(function (callable $get) {
                                                $sampleId = $get('contract_sample_id');
                                                if (! $sampleId) {
                                                    return false;
                                                }

                                                $sample = ContractSample::find($sampleId);

                                                return $sample?->cost_calculation_type === CostCalculationTypeEnum::VARIABLE_COUNT;
                                            }),

                                    ])
                                    ->columnSpanFull()

                                    // -----------------------------
                                    // ΕΜΦΑΝΙΣΗ ΜΟΝΟ ΓΙΑ VARIABLE
                                    // -----------------------------
                                    ->hidden(function (callable $get) {
                                        $sampleId = $get('contract_sample_id');

                                        if (!$sampleId) {
                                            return true;
                                        }

                                        $sample = \App\Models\ContractSample::find($sampleId);

                                        if (!$sample) {
                                            return true;
                                        }

                                        return ! in_array(
                                            $sample->cost_calculation_type,
                                            [
                                                CostCalculationTypeEnum::VARIABLE,
                                                CostCalculationTypeEnum::VARIABLE_COUNT,
                                            ],
                                            true
                                        );
                                    }),


                                // ➡️ ΑΛΛΑΓΗ 2: Το Section S5 (Κατάσταση & Παρατηρήσεις) μεταφέρθηκε εδώ
                                /*
                                |--------------------------------------------------------------------------
                                | ΚΑΤΑΣΤΑΣΗ & ΠΑΡΑΤΗΡΗΣΕΙΣ (S5) - ΤΕΛΕΥΤΑΙΟ
                                |--------------------------------------------------------------------------
                                */
                                Section::make('Κατάσταση & Παρατηρήσεις')
                                    ->icon('heroicon-o-rectangle-stack')
                                    ->columns(1)
                                    ->compact()
                                    ->schema([
                                        Textarea::make('comments')
                                            ->label('Παρατηρήσεις')
                                            ->rows(3)
                                            ->placeholder('Οποιαδήποτε επιπλέον πληροφορία...'),

                                        Select::make('status')
                                            ->label('Κατάσταση')
                                            ->options(RecordStatusEnum::class)
                                            ->default(RecordStatusEnum::Active->value)
                                            ->required(),
                                    ]),
                            ]),

                        // =======================================================
                        // 2. ΔΕΞΙΑ ΣΤΗΛΗ (1/3 - Συμπληρωματικά Στοιχεία)
                        // =======================================================
                        Grid::make(1) // Εσωτερικό Grid για στοίχιση των Sections
                            ->columnSpan(1)
                            ->schema([

                                /*
                                |--------------------------------------------------------------------------
                                | ΣΥΜΒΑΣΗ & ΠΛΗΡΟΦΟΡΙΕΣ (S3)
                                |--------------------------------------------------------------------------
                                */
                                // ➡️ ΑΛΛΑΓΗ 3: Αλλαγή τίτλου Section (Ο Πελάτης έφυγε)
                                Section::make('Σύμβαση & Πληροφορίες')
                                    ->icon('heroicon-o-clipboard-document-check')
                                    ->columns(1)
                                    ->compact()
                                    ->schema([

                                        // 🟦 Σύμβαση
                                        Select::make('contract_id')
                                            ->label('Σύμβαση')
                                            ->relationship('contract', 'title')
                                            ->searchable()
                                            ->preload()
                                            ->reactive()
                                            ->default(null)
                                            ->placeholder('Προαιρετική επιλογή...'),

                                        // 🟦 Κατηγορία Δειγμάτων Σύμβασης
                                        Select::make('contract_sample_id')
                                            ->label('Κατηγορία Δειγμάτων Σύμβασης')
                                            ->reactive()
                                            ->options(function (callable $get) {
                                                $contractId = $get('contract_id');
                                                if (!$contractId) {
                                                    return [];
                                                }

                                                return \App\Models\ContractSample::query()
                                                    ->where('contract_id', $contractId)
                                                    ->where('is_master', true)
                                                    ->with('category')
                                                    ->get()
                                                    ->unique('contract_sample_category_id')
                                                    ->mapWithKeys(fn ($sample) => [
                                                        $sample->id => $sample->category?->name,
                                                    ])
                                                    ->toArray();
                                            })
                                            ->disabled(fn (callable $get) => !$get('contract_id'))
                                            ->required(fn (callable $get) => filled($get('contract_id')))
                                            ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                                if (!$state) return;

                                                $labCat = $get('lab_sample_category_id');
                                                if (!$labCat) return;

                                                $sample = \App\Models\ContractSample::with('labCategories')->find($state);
                                                $compatible = $sample?->labCategories?->pluck('id')?->contains($labCat);

                                                if (!$compatible) {
                                                    $set('contract_sample_id', null);
                                                    \Filament\Notifications\Notification::make()
                                                        ->title('Μη συμβατή επιλογή')
                                                        ->body('Η επιλεγμένη κατηγορία δειγμάτων σύμβασης δεν περιλαμβάνει την κατηγορία δειγμάτων του εργαστηρίου.')
                                                        ->danger()
                                                        ->send();
                                                }

                                                if ($sample?->cost_calculation_type === CostCalculationTypeEnum::VARIABLE_COUNT) {
                                                    $set('analyses', []);
                                                    $set('analysis_package_id', null);
                                                }
                                            })
                                            ->placeholder('Επιλέξτε κατηγορία δειγμάτων σύμβασης...')
                                            ->helperText('Εμφανίζονται μόνο οι master κατηγορίες της σύμβασης'),


                                        // 🟧 Info box – Πληροφορίες για τη σύμβαση του πελάτη
                                        // Χρησιμοποιεί το customer_id που βρίσκεται πλέον στο S1
                                        TextEntry::make('customer_contract_info')
                                            ->label('Πληροφορίες Σύμβασης')
                                            ->columnSpanFull()
                                            ->html() // ✅ επιτρέπει HTML rendering
                                            ->color('gray')
                                            ->default(function (callable $get): HtmlString {
                                                $customerId = $get('customer_id');

                                                if (!$customerId) {
                                                    return new HtmlString('<em>Δεν έχει επιλεγεί πελάτης.</em>');
                                                }

                                                $contract = Contract::where('lab_customer_id', $customerId)
                                                    ->where('status', RecordStatusEnum::Active)
                                                    ->orderByDesc('date_start')
                                                    ->first();

                                                if (!$contract) {
                                                    return new HtmlString('<span class="text-red-700">Ο πελάτης δεν έχει ενεργή σύμβαση.</span>');
                                                }

                                                // Προαιρετικά, κάνε το clickable:
                                                $url = route('filament.admin.resources.contracts.view', $contract->id);

                                                return new HtmlString(sprintf(
                                                    '<span class="text-green-800 font-medium">Ενεργή σύμβαση: </span>
                                                    <a href="%s" target="_blank" class="text-primary-600 underline hover:text-primary-800">%s – %s</a><br>
                                                    <em>Διάρκεια: </em> %s έως %s',
                                                    e($url),
                                                    e($contract->contract_number ?? '—'),
                                                    e($contract->title ?? ''),
                                                    e($contract->date_start?->format('d/m/Y') ?? '-'),
                                                    e($contract->date_end?->format('d/m/Y') ?? '-')
                                                ));
                                            }),
                                    ]),

                                // ➡️ ΑΛΛΑΓΗ 2: Το Section S5 (Κατάσταση & Παρατηρήσεις) αφαιρέθηκε από εδώ

                            ]),
                    ]),
            ]);
    }
}
