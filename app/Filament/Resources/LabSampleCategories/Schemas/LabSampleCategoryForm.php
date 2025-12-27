<?php

namespace App\Filament\Resources\LabSampleCategories\Schemas;

use App\Enums\RecordStatusEnum;
// Structural components moved to the unified Schemas namespace in Filament 4.x
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema; // Core Schema object for standalone files

// Field components remain in the Forms namespace
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;


class LabSampleCategoryForm
{
    /**
     * Redesigned form schema for professional UX/UI, specifically for Filament 4.x standalone schemas.
     * Uses the required configure(Schema $schema) method signature with updated 4.x namespaces.
     */
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // Main Grid Container: Creates a 2:1 column layout (2/3 width for main, 1/3 for sidebar)
                Grid::make(3)
                    // This ensures the custom 3-column grid spans the entire width of the form.
                    ->columnSpanFull()
                    ->schema([

                        // === LEFT COLUMN (2/3 width) - Primary and Financial Data ===
                        Grid::make(1) // Single column container for main sections
                            ->columnSpan(2) // Span 2 out of 3 columns
                            ->schema([

                                // 1. ΒΑΣΙΚΑ ΣΤΟΙΧΕΙΑ (BASIC DETAILS)
                                Section::make('Βασικά στοιχεία')
                                    ->description('Καταχώριση των κύριων στοιχείων.') // Minimized text
                                    ->icon('heroicon-o-tag')
                                    ->compact()
                                    ->schema([
                                        Grid::make(3) // Inner grid for name/code layout
                                            ->schema([
                                                TextInput::make('name')
                                                    ->label('Ονομασία')
                                                    ->required()
                                                    ->maxLength(255)
                                                    ->columnSpan(2)
                                                    , // Hint removed

                                                TextInput::make('code')
                                                    ->label('Κωδικός')
                                                    ->maxLength(50)
                                                    ->columnSpan(1)
                                                    , // Hint removed
                                            ]),

                                        Textarea::make('description')
                                            ->label('Περιγραφή')
                                            ->rows(3)
                                            ->placeholder('Προαιρετικά, σύντομη περιγραφή του περιεχομένου ή του σκοπού.')
                                            ->columnSpanFull(),
                                    ]),

                                // 3. ΟΙΚΟΝΟΜΙΚΑ & ΠΡΟΤΥΠΑ (FINANCIAL & STANDARDS)
                                Section::make('Πρόσθετα Στοιχεία')
                                    ->description('Δεδομένα τιμών και επιστημονικές αναφορές.') // Minimized text
                                    ->icon('heroicon-o-adjustments-vertical')
                                    ->compact()
                                    ->schema([

                                        // Fieldset for Price/Currency grouping
                                        Fieldset::make('Οικονομικά Στοιχεία')
                                            ->schema([
                                                TextInput::make('default_price')
                                                    ->label('Τιμή (€)')
                                                    ->numeric()
                                                    ->step(0.01)
                                                    ->prefix('€')
                                                    ->default(0.00)
                                                    ->required()
                                                    , // Hint removed

                                                TextInput::make('currency_code')
                                                    ->label('Νόμισμα')
                                                    ->maxLength(3)
                                                    ->default('EUR')
                                                    , // Hint removed
                                            ]),

                                        // Fieldset for Reference grouping
                                        Fieldset::make('Επιστημονικές Αναφορές')
                                            ->schema([
                                                TextInput::make('method_ref')
                                                    ->label('Μέθοδος αναφοράς')
                                                    ->maxLength(255)
                                                    ->placeholder('-')
                                                    , // Hint removed

                                                TextInput::make('standard_ref')
                                                    ->label('Πρότυπο / Νομοθεσία')
                                                    ->maxLength(255)
                                                    ->placeholder('-')
                                                    , // Hint removed
                                            ]),
                                    ]),
                            ]),

                        // === RIGHT COLUMN (1/3 width) - Relations and Status ===
                        Grid::make(1)
                            ->columnSpan(1)
                            ->schema([
                                // 2. ΑΝΤΙΣΤΟΙΧΙΣΕΙΣ (MAPPINGS / RELATIONS) - MOVED RIGHT AND MODIFIED
                                Section::make('Αντιστοιχίσεις & Σύνδεση')
                                    ->description('Σύνδεση με το εργαστήριο και τον τύπο δείγματος.')
                                    ->icon('heroicon-o-link')
                                    ->compact()
                                    ->schema([
                                        Grid::make(1) // 1 column for full width fields
                                            ->schema([
                                                Select::make('lab_id')
                                                    ->label('Εργαστήριο')
                                                    ->relationship('lab', 'name')
                                                    // searchable removed
                                                    ->required()
                                                    ->placeholder('Επιλέξτε εργαστήριο')
                                                    ->columnSpanFull()
                                                    , // Hint removed

                                                Select::make('sample_type_id')
                                                    ->label('Τύπος δείγματος')
                                                    ->relationship('sampleType', 'name')
                                                    // searchable removed
                                                    ->required()
                                                    ->placeholder('Επιλέξτε τύπο δείγματος')
                                                    ->columnSpanFull()
                                                    , // Hint removed
                                            ]),
                                    ]),

                                // 4. ΚΑΤΑΣΤΑΣΗ (STATUS) - REMAINS ON RIGHT
                                Section::make('Κατάσταση Κατηγορίας')
                                    ->description('Ενεργοποίηση ή απενεργοποίηση.')
                                    ->icon('heroicon-o-power')
                                    ->compact()
                                    ->schema([
                                        Select::make('status')
                                            ->label('Κατάσταση')
                                            ->options(collect(RecordStatusEnum::cases())->mapWithKeys(
                                                fn($case) => [$case->value => $case->getLabel()]
                                            ))
                                            ->default(RecordStatusEnum::Active->value)
                                            ->required()
                                            , // Hint removed
                                    ]),
                            ]),
                    ]),
            ]);
    }
}
