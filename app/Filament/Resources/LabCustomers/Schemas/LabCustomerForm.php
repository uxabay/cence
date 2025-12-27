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
                // Main Grid Container: Creates a 2:1 column layout (2/3 width for main, 1/3 for sidebar)
                Grid::make(3)
                    ->columnSpanFull()
                    ->schema([

                        // === LEFT COLUMN (2/3 width) - Primary, Contact, Address, and Notes Data ===
                        Grid::make(1) // Single column container for main sections
                            ->columnSpan(2) // Span 2 out of 3 columns
                            ->schema([

                                // 1. Βασικές Πληροφορίες & Διεύθυνση - Ενοποίηση των δύο ενοτήτων
                                Section::make('Βασικές Πληροφορίες & Διεύθυνση')
                                    ->description('Επωνυμία, κατηγορία, και πλήρης διεύθυνση πελάτη.') // Updated description
                                    ->icon('heroicon-o-user-group')
                                    ->compact()
                                    ->schema([
                                        // Row 1: Name and Category (2 columns)
                                        Grid::make(2)
                                            ->schema([
                                                TextInput::make('name')
                                                    ->label('Επωνυμία')
                                                    ->required()
                                                    ->maxLength(255)
                                                    ->placeholder('Εισάγετε την επωνυμία του πελάτη'),

                                                Select::make('customer_category_id')
                                                    ->label('Κατηγορία')
                                                    ->options(CustomerCategory::query()->pluck('name', 'id'))
                                                    ->searchable()
                                                    ->required()
                                                    ->placeholder('Επιλέξτε κατηγορία πελάτη'),
                                            ]),

                                        // Row 2: Address fields (3 columns) - Μετακινήθηκαν εδώ
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
                                    ]),

                                // 2. Επικοινωνία (Unchanged)
                                Section::make('Επικοινωνία')
                                    ->description('Στοιχεία επικοινωνίας και υπεύθυνος.')
                                    ->icon('heroicon-o-phone')
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
                                                    ->placeholder('π.χ. info@domain.gr'),
                                            ]),
                                        // Encryption key remains full-width
                                        TextInput::make('encryption_key')
                                            ->label('Κλειδί κρυπτογράφησης')
                                            ->maxLength(255)
                                            ->placeholder('Εισάγετε το κλειδί αν χρησιμοποιείται')
                                            ->columnSpanFull(),
                                    ]),

                                // 3. Σημειώσεις (New Section, moved from right column)
                                Section::make('Σημειώσεις')
                                    ->description('Προαιρετικές πληροφορίες για τον πελάτη.')
                                    ->icon('heroicon-o-document-text')
                                    ->compact()
                                    ->schema([
                                        Textarea::make('notes')
                                            ->label('Σημειώσεις')
                                            ->rows(5)
                                            ->placeholder('Προσθέστε προαιρετικές πληροφορίες για τον πελάτη.')
                                            ->columnSpanFull(),
                                    ]),
                            ]),


                        // === RIGHT COLUMN (1/3 width) - Financial and Status Data ===
                        Grid::make(1)
                            ->columnSpan(1)
                            ->schema([

                                // 4. Οικονομικά, Σύστημα & Κατάσταση
                                Section::make('Οικονομικά, Σύστημα & Κατάσταση')
                                    ->description('Φορολογικά, οργανωτικά στοιχεία και διαχείριση κατάστασης.')
                                    ->icon('heroicon-o-briefcase')
                                    ->compact()
                                    ->schema([
                                        // Status
                                        Select::make('status')
                                            ->label('Κατάσταση')
                                            ->options(
                                                collect(CustomerStatusEnum::cases())
                                                    ->mapWithKeys(fn($case) => [$case->value => $case->getLabel()])
                                            )
                                            ->default(CustomerStatusEnum::Active->value)
                                            ->required(),

                                        TextInput::make('tax_id')
                                            ->label('Α.Φ.Μ.')
                                            ->maxLength(20)
                                            ->placeholder('π.χ. 099999999'),

                                        TextInput::make('organization_code')
                                            ->label('Κωδικός Οργάνωσης')
                                            ->maxLength(50)
                                            ->placeholder('π.χ. ORG-001'),

                                    ])
                                    ->columns(1), // Stacking fields vertically in the sidebar

                                // Removed Section 5: Λοιπά στοιχεία (Notes moved left, last_update_at removed)

                            ]),
                    ]),
            ]);
    }
}
