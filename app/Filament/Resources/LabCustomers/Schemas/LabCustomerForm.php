<?php

namespace App\Filament\Resources\LabCustomers\Schemas;

use App\Enums\CustomerStatusEnum;
use App\Filament\Resources\LabCustomers\Components\CustomerEmailsForm;
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
                // 🟩 1. Γενικά στοιχεία
                Section::make('Γενικά στοιχεία')
                    ->icon('heroicon-o-user-group')
                    ->compact()
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('name')
                                    ->label('Ονομασία')
                                    ->required()
                                    ->maxLength(255),

                                Select::make('customer_category_id')
                                    ->label('Κατηγορία')
                                    ->options(CustomerCategory::query()->pluck('name', 'id'))
                                    ->searchable()
                                    ->required(),

                                Select::make('status')
                                    ->label('Κατάσταση')
                                    ->options(collect(CustomerStatusEnum::cases())->mapWithKeys(fn($case) => [$case->value => $case->getLabel()]))
                                    ->default(CustomerStatusEnum::Active->value)
                                    ->required(),
                            ]),
                    ])
                    ->columnSpanFull(),

                // 🟦 2. Στοιχεία επικοινωνίας
                Section::make('Στοιχεία επικοινωνίας')
                    ->icon('heroicon-o-phone')
                    ->collapsible()
                    ->compact()
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('contact_person')
                                    ->label('Υπεύθυνος επικοινωνίας')
                                    ->maxLength(255),

                                TextInput::make('phone')
                                    ->label('Τηλέφωνο')
                                    ->tel()
                                    ->maxLength(50),

                                TextInput::make('encryption_key')
                                    ->label('Κλειδί κρυπτογράφησης')
                                    ->helperText('Αν εφαρμόζεται κρυπτογραφημένη επικοινωνία.'),
                            ])
                            ->columnSpanFull(),

                        Fieldset::make('Διεύθυνση')
                            ->schema([
                                Grid::make(3)
                                    ->schema([
                                        TextInput::make('address')
                                            ->label('Διεύθυνση')
                                            ->maxLength(255),

                                        TextInput::make('postal_code')
                                            ->label('Τ.Κ.')
                                            ->maxLength(20),

                                        TextInput::make('city')
                                            ->label('Πόλη')
                                            ->maxLength(100),
                                    ])
                                    ->columnSpanFull(),
                            ]),
                    ])
                    ->columnSpanFull(),

                // 🟨 3. Emails επικοινωνίας (το component)
                CustomerEmailsForm::make(),

                // 🟪 4. Πρόσθετα στοιχεία
                Section::make('Πρόσθετα στοιχεία')
                    ->icon('heroicon-o-clock')
                    ->collapsible()
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                DateTimePicker::make('last_update_at')
                                    ->label('Τελευταία ενημέρωση'),

                                Textarea::make('notes')
                                    ->label('Σημειώσεις')
                                    ->rows(3)
                                    ->columnSpanFull(),
                            ]),
                    ]),
            ]);
    }
}
