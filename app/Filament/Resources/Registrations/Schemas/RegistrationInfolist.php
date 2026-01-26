<?php

namespace App\Filament\Resources\Registrations\Schemas;

use App\Models\Registration;
use App\Filament\Resources\Registrations\Schemas\Components\RegistrationPricingSection;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;

class RegistrationInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([

            Grid::make(3)
                ->columnSpanFull()
                ->schema([

                    /*
                    |--------------------------------------------------------------------------
                    | LEFT COLUMN (2/3)
                    |--------------------------------------------------------------------------
                    */
                    Grid::make(1)
                        ->columnSpan(2)
                        ->schema([

                            /*
                            |--------------------------------------------------------------------------
                            | S1 – Στοιχεία Πρωτοκόλλου & Πελάτη
                            |--------------------------------------------------------------------------
                            */
                            Section::make('Στοιχεία Πρωτοκόλλου & Πελάτη')
                                ->icon('heroicon-o-document-text')
                                ->compact()
                                ->schema([
                                    Grid::make(3)->schema([
                                        TextEntry::make('registration_number')
                                            ->label('Αριθμός Πρωτοκόλλου')
                                            ->weight('medium')
                                            ->color('primary'),

                                        TextEntry::make('date')
                                            ->label('Ημερομηνία')
                                            ->date(),

                                        TextEntry::make('year')
                                            ->label('Έτος'),
                                    ]),

                                    Grid::make(2)->schema([
                                        TextEntry::make('customer.name')
                                            ->label('Πελάτης')
                                            ->weight('medium')
                                            ->columnSpan(2),
                                    ]),
                                ]),

                            /*
                            |--------------------------------------------------------------------------
                            | S2 – Δείγματα Εργαστηρίου
                            |--------------------------------------------------------------------------
                            */
                            Section::make('Δείγματα Εργαστηρίου')
                                ->icon('heroicon-o-beaker')
                                ->compact()
                                ->schema([
                                    Grid::make(2)->schema([
                                        TextEntry::make('labCategory.name')
                                            ->label('Κατηγορία Δείγματος')
                                            ->placeholder('-')
                                            ->columnSpan(2),
                                    ]),

                                    Grid::make(3)->schema([
                                        TextEntry::make('num_samples_received')
                                            ->label('Ληφθέντα Δείγματα')
                                            ->badge()
                                            ->numeric(),

                                        TextEntry::make('not_valid_samples')
                                            ->label('Ακατάλληλα')
                                            ->badge()
                                            ->color('danger')
                                            ->numeric(),

                                        TextEntry::make('total_samples')
                                            ->label('Έγκυρα')
                                            ->badge()
                                            ->numeric()
                                            ->weight('medium'),
                                    ]),
                                ]),

                            /*
                            |--------------------------------------------------------------------------
                            | S4 – Αναλύσεις
                            |--------------------------------------------------------------------------
                            */
                            Section::make('Αναλύσεις')
                                ->icon('heroicon-o-chart-bar')
                                ->description('Συνοπτική παρουσίαση των αναλύσεων του πρωτοκόλλου')
                                ->compact()
                                ->hidden(function (Registration $record) {
                                    return $record->contractSample?->cost_calculation_type !== \App\Enums\CostCalculationTypeEnum::VARIABLE;
                                })
                                ->schema([

                                    Grid::make(3)->schema([
                                        TextEntry::make('analyses_count')
                                            ->label('Πλήθος Αναλύσεων')
                                            ->numeric()
                                            ->badge()
                                            ->color('info')
                                            ->placeholder('0')
                                            ->columnSpan(1),
                                    ]),

                                    TextEntry::make('analyses_summary')
                                        ->label('Σύνοψη Αναλύσεων')
                                        ->html()
                                        ->getStateUsing(function (Registration $record) {

                                            if ($record->analyses->isEmpty()) {
                                                return '-';
                                            }

                                            return $record->analyses
                                                ->map(fn ($a) =>
                                                    e($a->analysis_name)
                                                    . ' (<strong>' . number_format($a->analysis_price, 2) . ' €</strong>)'
                                                )
                                                ->join(', ');
                                        })
                                        ->columnSpanFull(),
                                ]),


                            /*
                            |--------------------------------------------------------------------------
                            | S5 – Κατάσταση & Παρατηρήσεις
                            |--------------------------------------------------------------------------
                            */
                            Section::make('Κατάσταση & Παρατηρήσεις')
                                ->icon('heroicon-o-rectangle-stack')
                                ->compact()
                                ->schema([
                                    Grid::make(2)->schema([
                                        TextEntry::make('status')
                                            ->label('Κατάσταση')
                                            ->badge()
                                            ->color(fn ($state) =>
                                                $state === 'active' ? 'success' : 'gray'
                                            )
                                            ->columnSpan(1),
                                    ]),

                                    TextEntry::make('comments')
                                        ->label('Παρατηρήσεις')
                                        ->placeholder('-')
                                        ->columnSpanFull(),
                                ]),
                        ]),

                    /*
                    |--------------------------------------------------------------------------
                    | RIGHT COLUMN (1/3)
                    |--------------------------------------------------------------------------
                    */
                    Grid::make(1)
                        ->columnSpan(1)
                        ->schema([

                            /*
                            |--------------------------------------------------------------------------
                            | S3 – Σύμβαση & Γραμμή Σύμβασης
                            |--------------------------------------------------------------------------
                            */
                            Section::make('Σύμβαση & Πληροφορίες')
                                ->icon('heroicon-o-clipboard-document-check')
                                ->compact()
                                ->schema([
                                    TextEntry::make('contract.title')
                                        ->label('Σύμβαση')
                                        ->weight('medium')
                                        ->placeholder('-'),

                                    TextEntry::make('contractSample.category.name')
                                        ->label('Κατηγορία Δειγμάτων Σύμβασης')
                                        ->placeholder('-'),

                                    TextEntry::make('contractSample.cost_calculation_type')
                                        ->label('Τύπος Κόστους')
                                        ->badge()
                                        ->color(fn ($state) => match ($state) {
                                            \App\Enums\CostCalculationTypeEnum::VARIABLE => 'warning',
                                            \App\Enums\CostCalculationTypeEnum::VARIABLE_COUNT => 'info',
                                            default => 'success',
                                        }),

                                    TextEntry::make('contractSample.max_analyses')
                                        ->label('Μέγιστο Όριο Αναλύσεων')
                                        ->placeholder('-')
                                        ->hidden(fn (Registration $record) =>
                                            ! in_array(
                                                $record->contractSample?->cost_calculation_type,
                                                [
                                                    \App\Enums\CostCalculationTypeEnum::VARIABLE,
                                                    \App\Enums\CostCalculationTypeEnum::VARIABLE_COUNT,
                                                ],
                                                true
                                            )
                                        ),

                                    TextEntry::make('customer_contract_info')
                                        ->label('Πληροφορίες Σύμβασης Πελάτη')
                                        ->html()
                                        ->columnSpanFull(),
                                ]),

                            /*
                            |--------------------------------------------------------------------------
                            | Οικονομικά Πρωτοκόλλου – Updated
                            |--------------------------------------------------------------------------
                            */
                            RegistrationPricingSection::make(),


                            /*
                            |--------------------------------------------------------------------------
                            | S6 – Στοιχεία Καταγραφής
                            |--------------------------------------------------------------------------
                            */
                            Section::make('Στοιχεία Καταγραφής')
                                ->icon('heroicon-o-information-circle')
                                ->compact()
                                ->schema([
                                    TextEntry::make('created_at')
                                        ->label('Δημιουργήθηκε')
                                        ->dateTime('d/m/Y H:i')
                                        ->placeholder('-'),

                                    TextEntry::make('updated_at')
                                        ->label('Τροποποιήθηκε')
                                        ->dateTime('d/m/Y H:i')
                                        ->placeholder('-'),

                                    TextEntry::make('createdBy.name')
                                        ->label('Καταχωρήθηκε από')
                                        ->placeholder('-'),

                                    TextEntry::make('updatedBy.name')
                                        ->label('Τροποποιήθηκε από')
                                        ->placeholder('-'),
                                ]),
                        ]),
                ]),
        ]);
    }
}
