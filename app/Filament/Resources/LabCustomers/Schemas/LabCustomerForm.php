<?php

namespace App\Filament\Resources\LabCustomers\Schemas;

use App\Enums\CustomerStatusEnum;
use App\Models\CustomerCategory;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;

class LabCustomerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                /*
                |--------------------------------------------------------------------------
                | 🟩 1. Βασικές Πληροφορίες
                |--------------------------------------------------------------------------
                */
                Section::make('Βασικές Πληροφορίες')
                    ->icon('heroicon-o-user-group')
                    ->compact()
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('name')
                                    ->label('Επωνυμία')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('Εισάγετε την επωνυμία του πελάτη')
                                    ->helperText('Επίσημη ονομασία του πελάτη.'),

                                Select::make('customer_category_id')
                                    ->label('Κατηγορία')
                                    ->options(CustomerCategory::query()->pluck('name', 'id'))
                                    ->searchable()
                                    ->required()
                                    ->placeholder('Επιλέξτε κατηγορία πελάτη'),

                                Select::make('status')
                                    ->label('Κατάσταση')
                                    ->options(
                                        collect(CustomerStatusEnum::cases())
                                            ->mapWithKeys(fn($case) => [$case->value => $case->getLabel()])
                                    )
                                    ->default(CustomerStatusEnum::Active->value)
                                    ->required()
                                    ->helperText('Ορίζει αν ο πελάτης είναι ενεργός, ανενεργός ή αρχειοθετημένος.'),
                            ]),
                    ])
                    ->columnSpanFull(),

                /*
                |--------------------------------------------------------------------------
                | 🟦 2. Επικοινωνία
                |--------------------------------------------------------------------------
                */
                Section::make('Επικοινωνία')
                    ->icon('heroicon-o-phone')
                    ->collapsible()
                    ->compact()
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('contact_person')
                                    ->label('Υπεύθυνος επικοινωνίας')
                                    ->maxLength(255)
                                    ->placeholder('Ονοματεπώνυμο υπευθύνου'),

                                TextInput::make('phone')
                                    ->label('Τηλέφωνο')
                                    ->tel()
                                    ->maxLength(50)
                                    ->placeholder('π.χ. 2410 123456'),

                                TextInput::make('email_primary')
                                    ->label('Κύριο Email')
                                    ->email()
                                    ->maxLength(255)
                                    ->placeholder('π.χ. info@domain.gr')
                                    ->helperText('Το κύριο email επικοινωνίας.'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('encryption_key')
                                    ->label('Κλειδί κρυπτογράφησης')
                                    ->maxLength(255)
                                    ->placeholder('Εισάγετε το κλειδί αν χρησιμοποιείται')
                                    ->helperText('Συμπληρώνεται μόνο αν εφαρμόζεται κρυπτογραφημένη επικοινωνία.'),
                            ]),
                    ])
                    ->columnSpanFull(),

                /*
                |--------------------------------------------------------------------------
                | 🏠 3. Διεύθυνση
                |--------------------------------------------------------------------------
                */
                Section::make('Διεύθυνση')
                    ->icon('heroicon-o-map-pin')
                    ->collapsible()
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('address')
                                    ->label('Διεύθυνση')
                                    ->maxLength(255)
                                    ->placeholder('Οδός & αριθμός'),

                                TextInput::make('postal_code')
                                    ->label('Τ.Κ.')
                                    ->maxLength(20)
                                    ->placeholder('π.χ. 41222'),

                                TextInput::make('city')
                                    ->label('Πόλη')
                                    ->maxLength(100)
                                    ->placeholder('π.χ. Λάρισα'),
                            ]),
                    ])
                    ->columnSpanFull(),

                /*
                |--------------------------------------------------------------------------
                | 💼 4. Οικονομικά & Σύστημα
                |--------------------------------------------------------------------------
                */
                Section::make('Οικονομικά & Σύστημα')
                    ->icon('heroicon-o-briefcase')
                    ->collapsible()
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('tax_id')
                                    ->label('Α.Φ.Μ.')
                                    ->maxLength(20)
                                    ->placeholder('π.χ. 099999999')
                                    ->helperText('Αριθμός Φορολογικού Μητρώου πελάτη.'),

                                TextInput::make('organization_code')
                                    ->label('Κωδικός Οργάνωσης')
                                    ->maxLength(50)
                                    ->placeholder('π.χ. ORG-001'),

                                TextInput::make('created_by')
                                    ->label('Δημιουργήθηκε από')
                                    ->disabled()
                                    ->visible(false),
                            ]),
                    ])
                    ->columnSpanFull(),

                /*
                |--------------------------------------------------------------------------
                | 🟪 5. Λοιπά Στοιχεία
                |--------------------------------------------------------------------------
                */
                Section::make('Λοιπά στοιχεία')
                    ->icon('heroicon-o-clock')
                    ->collapsible()
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                DateTimePicker::make('last_update_at')
                                    ->label('Τελευταία ενημέρωση')
                                    ->disabled()
                                    ->helperText('Συμπληρώνεται αυτόματα από το σύστημα.'),

                                Textarea::make('notes')
                                    ->label('Σημειώσεις')
                                    ->rows(3)
                                    ->placeholder('Προσθέστε προαιρετικές πληροφορίες για τον πελάτη.')
                                    ->columnSpanFull(),
                            ]),
                    ]),
            ]);
    }
}
