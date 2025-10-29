<?php

namespace App\Filament\Resources\Registrations\Schemas;

use App\Enums\RecordStatusEnum;
use App\Models\ContractSample;
use App\Models\Registration;
use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;
use Illuminate\Support\Carbon;
use Filament\Notifications\Notification;

class RegistrationForm
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
                    ->columns(3)
                    ->schema([
                        DatePicker::make('date')
                            ->label('Ημερομηνία')
                            ->required()
                            ->default(today())
                            ->displayFormat('d/m/Y')
                            ->native(false)
                            ->closeOnDateSelection()
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set) {
                                if (filled($state)) {
                                    $set('year', \Illuminate\Support\Carbon::parse($state)->year);
                                }
                            }),

                        TextInput::make('registration_number')
                            ->label('Αριθμός Πρωτοκόλλου')
                            ->required()
                            ->maxLength(50)
                            ->placeholder('π.χ. Π-045/2025')
                            ->columnSpan(1),

                        TextInput::make('year')
                            ->label('Έτος')
                            ->numeric()
                            ->readOnly()
                            ->dehydrated()
                            ->columnSpan(1)
                            ->suffixIcon('heroicon-o-calendar'),
                    ])
                    ->columnSpanFull(),

                /*
                |--------------------------------------------------------------------------
                | ΔΕΙΓΜΑΤΑ ΕΡΓΑΣΤΗΡΙΟΥ
                |--------------------------------------------------------------------------
                */
                Section::make('Δείγματα Εργαστηρίου')
                    ->icon('heroicon-o-beaker')
                    ->columns(3)
                    ->schema([

                        Grid::make(2)
                            ->schema([
                                Select::make('lab_sample_category_id')
                                    ->label('Κατηγορία Δείγματος Εργαστηρίου')
                                    ->relationship('labCategory', 'name')
                                    ->required()
                                    ->preload()
                                    ->searchable()
                                    ->columnSpan(1)
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
                    ])
                    ->columnSpanFull(),

                /*
                |--------------------------------------------------------------------------
                | ΠΕΛΑΤΗΣ & ΣΥΜΒΑΣΗ
                |--------------------------------------------------------------------------
                */
                Section::make('Πελάτης & Σύμβαση')
                    ->icon('heroicon-o-clipboard-document-check')
                    ->columns(3)
                    ->schema([

                        Select::make('customer_id')
                            ->label('Πελάτης')
                            ->relationship('customer', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->columnSpan(1)
                            ->placeholder('Επιλέξτε πελάτη...'),

                        Select::make('contract_id')
                            ->label('Σύμβαση')
                            ->relationship('contract', 'title')
                            ->searchable()
                            ->preload()
                            ->reactive()
                            ->default(null)
                            ->placeholder('Προαιρετική επιλογή...')
                            ->columnSpan(1),

                        Select::make('contract_sample_id')
                            ->label('Κατηγορία Δειγμάτων Σύμβασης')
                            ->reactive()
                            ->options(function (callable $get) {
                                $contractId = $get('contract_id');
                                if (!$contractId) {
                                    return [];
                                }

                                return ContractSample::query()
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

                                // Έλεγχος συμβατότητας μεταξύ labCategory και contractSample
                                $sample = ContractSample::with('labCategories')
                                    ->find($state);

                                $compatible = $sample?->labCategories
                                    ?->pluck('id')
                                    ?->contains($labCat);

                                if (!$compatible) {
                                    $set('contract_sample_id', null);
                                    Notification::make()
                                        ->title('Μη συμβατή επιλογή')
                                        ->body('Η επιλεγμένη κατηγορία δειγμάτων σύμβασης δεν περιλαμβάνει την κατηγορία δειγμάτων του εργαστηρίου.')
                                        ->danger()
                                        ->send();
                                }
                            })
                            ->placeholder('Επιλέξτε κατηγορία δειγμάτων σύμβασης...')
                            ->helperText('Εμφανίζονται μόνο οι master κατηγορίες της σύμβασης')
                            ->columnSpan(1),
                    ])
                    ->columnSpanFull(),

                /*
                |--------------------------------------------------------------------------
                | ΠΑΡΑΤΗΡΗΣΕΙΣ & ΚΑΤΑΣΤΑΣΗ
                |--------------------------------------------------------------------------
                */
                Section::make('Κατάσταση & Παρατηρήσεις')
                    ->icon('heroicon-o-rectangle-stack')
                    ->columns(2)
                    ->schema([
                        Textarea::make('comments')
                            ->label('Παρατηρήσεις')
                            ->rows(3)
                            ->placeholder('Οποιαδήποτε επιπλέον πληροφορία...')
                            ->columnSpanFull(),

                        Select::make('status')
                            ->label('Κατάσταση')
                            ->options(RecordStatusEnum::class)
                            ->default(RecordStatusEnum::Active->value)
                            ->required()
                            ->columnSpan(1),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
