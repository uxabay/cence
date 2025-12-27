<?php

namespace App\Filament\Resources\LabSampleCategories\Schemas;

use App\Enums\RecordStatusEnum;
// Structural components moved to the unified Schemas namespace in Filament 4.x
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema; // Core Schema object for standalone files

// Infolist components
use Filament\Infolists\Components\TextEntry;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\FontWeight;

class LabSampleCategoryInfolist
{
    /**
     * Infolist schema with professional 2:1 column layout, matching the Form structure.
     * Uses the required configure(Schema $schema) method signature with updated 4.x namespaces.
     */
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // Main Grid Container: Creates a 2:1 column layout (2/3 width for main, 1/3 for sidebar)
                Grid::make(3)
                    // Ensures the custom 3-column grid spans the entire width of the infolist.
                    ->columnSpanFull()
                    ->schema([

                        // === LEFT COLUMN (2/3 width) - Primary and Financial Data ===
                        Grid::make(1) // Single column container for main sections
                            ->columnSpan(2) // Span 2 out of 3 columns
                            ->schema([

                                // 1. ΒΑΣΙΚΑ ΣΤΟΙΧΕΙΑ (BASIC DETAILS)
                                Section::make('Βασικά στοιχεία')
                                    ->description('Κύριες πληροφορίες της κατηγορίας.')
                                    ->icon('heroicon-o-tag')
                                    ->compact()
                                    ->schema([
                                        Grid::make(3) // Inner grid for name/code layout
                                            ->schema([
                                                TextEntry::make('name')
                                                    ->label('Ονομασία')
                                                    ->weight(FontWeight::SemiBold)
                                                    ->columnSpan(2),

                                                TextEntry::make('code')
                                                    ->label('Κωδικός')
                                                    ->placeholder('Δεν έχει οριστεί')
                                                    ->columnSpan(1),
                                            ]),

                                        TextEntry::make('description')
                                            ->label('Περιγραφή')
                                            ->placeholder('Δεν υπάρχει λεπτομερής περιγραφή.')
                                            ->columnSpanFull(),
                                    ]),

                                // 3. ΟΙΚΟΝΟΜΙΚΑ & ΠΡΟΤΥΠΑ (FINANCIAL & STANDARDS)
                                Section::make('Πρόσθετα Στοιχεία')
                                    ->description('Δεδομένα τιμών και επιστημονικές αναφορές.')
                                    ->icon('heroicon-o-adjustments-vertical')
                                    ->compact()
                                    ->schema([

                                        // Fieldset for Price/Currency grouping
                                        Fieldset::make('Οικονομικά Στοιχεία')
                                            ->schema([
                                                TextEntry::make('default_price')
                                                    ->label('Τιμή')
                                                    ->money('eur') // Display as Euro currency
                                                    ->formatStateUsing(fn (?float $state, $record) => number_format($state, 2) . ' ' . $record->currency_code),


                                                TextEntry::make('currency_code')
                                                    ->label('Νόμισμα'),
                                            ]),

                                        // Fieldset for Reference grouping
                                        Fieldset::make('Επιστημονικές Αναφορές')
                                            ->schema([
                                                TextEntry::make('method_ref')
                                                    ->label('Μέθοδος αναφοράς')
                                                    ->placeholder('-'),

                                                TextEntry::make('standard_ref')
                                                    ->label('Πρότυπο / Νομοθεσία')
                                                    ->placeholder('-'),
                                            ]),
                                    ]),
                            ]),

                        // === RIGHT COLUMN (1/3 width) - Relations and Status ===
                        Grid::make(1)
                            ->columnSpan(1)
                            ->schema([
                                // 2. ΑΝΤΙΣΤΟΙΧΙΣΕΙΣ (MAPPINGS / RELATIONS)
                                Section::make('Αντιστοιχίσεις & Σύνδεση')
                                    ->description('Σχέσεις με Εργαστήριο και Τύπο Δείγματος.')
                                    ->icon('heroicon-o-link')
                                    ->compact()
                                    ->schema([
                                        TextEntry::make('lab.name')
                                            ->label('Εργαστήριο')
                                            ->badge()
                                            ->color(Color::Blue),

                                        TextEntry::make('sampleType.name')
                                            ->label('Τύπος Δείγματος')
                                            ->badge()
                                            ->color(Color::Emerald),
                                    ]),

                                // 4. ΚΑΤΑΣΤΑΣΗ (STATUS)
                                Section::make('Κατάσταση Κατηγορίας')
                                    ->description('Ενεργοποίηση ή απενεργοποίηση.')
                                    ->icon('heroicon-o-power')
                                    ->compact()
                                    ->schema([
                                        TextEntry::make('status')
                                            ->label('Κατάσταση')
                                            ->badge()
                                            ->formatStateUsing(fn ($state): string => $state->getLabel())
                                            ->color(fn ($state): string => match ($state) {
                                                RecordStatusEnum::Active => 'success',
                                                RecordStatusEnum::Inactive => 'danger',
                                                default => 'gray',
                                            }),
                                    ]),
                            ]),
                    ]),
            ]);
    }
}
