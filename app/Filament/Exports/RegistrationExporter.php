<?php

namespace App\Filament\Exports;

use App\Models\Registration;
use App\Support\Pricing\RegistrationPricingPresenter;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Number;

class RegistrationExporter extends Exporter
{
    protected static ?string $model = Registration::class;

    /**
     * Respect table filters/sorting and ensure query integrity.
     * Only eager-load relations (no filtering here).
     */
    public static function modifyQuery(Builder $query): Builder
    {
        return $query->with([
            'customer',
            'contract',
            'labCategory',
            'contractSample.category',
        ]);
    }

    public static function getColumns(): array
    {
        return [

            /*
            |--------------------------------------------------------------------------
            | Πρωτόκολλο
            |--------------------------------------------------------------------------
            */
            ExportColumn::make('date')
                ->label('Ημερομηνία'),

            ExportColumn::make('registration_number')
                ->label('Αρ. Πρωτοκόλλου'),

            ExportColumn::make('year')
                ->label('Έτος'),

            ExportColumn::make('status')
                ->label('Κατάσταση')
                ->formatStateUsing(fn ($state) => $state?->getLabel() ?? '—'),

            /*
            |--------------------------------------------------------------------------
            | Πελάτης / Σύμβαση
            |--------------------------------------------------------------------------
            */
            ExportColumn::make('customer.name')
                ->label('Πελάτης'),

            ExportColumn::make('contract.contract_number')
                ->label('Αρ. Σύμβασης'),

            /*
            |--------------------------------------------------------------------------
            | Κατηγορίες Δείγματος (έλεγχος αντιστοίχισης)
            |--------------------------------------------------------------------------
            */
            ExportColumn::make('labCategory.name')
                ->label('Κατηγορία Εργαστηριακού Δείγματος'),

            ExportColumn::make('contractSample.category.name')
                ->label('Κατηγορία Δείγματος Σύμβασης'),

            /*
            |--------------------------------------------------------------------------
            | Δείγματα
            |--------------------------------------------------------------------------
            */
            ExportColumn::make('num_samples_received')
                ->label('Παραληφθέντα'),

            ExportColumn::make('not_valid_samples')
                ->label('Μη έγκυρα'),

            ExportColumn::make('total_samples')
                ->label('Έγκυρα'),

            /*
            |--------------------------------------------------------------------------
            | Τιμολόγηση (snapshot v1.1.0)
            |--------------------------------------------------------------------------
            */
            ExportColumn::make('pricing_type')
                ->label('Τύπος Τιμολόγησης')
                ->state(fn (Registration $record) =>
                    RegistrationPricingPresenter::from($record)->toTable()['pricing_label']
                ),

            ExportColumn::make('analyses_count')
                ->label('Αναλύσεις ανά δείγμα'),

            ExportColumn::make('analysis_unit_price_snapshot')
                ->label('Τιμή Ανάλυσης')
                ->formatStateUsing(fn ($state) => $state !== null ? (float) $state : null),

            ExportColumn::make('calculated_unit_price')
                ->label('Τιμή Δείγματος')
                ->formatStateUsing(fn ($state) => $state !== null ? (float) $state : null),

            ExportColumn::make('calculated_total')
                ->label('Σύνολο')
                ->formatStateUsing(fn ($state) => $state !== null ? (float) $state : null),

            ExportColumn::make('currency_code')
                ->label('Νόμισμα'),

            ExportColumn::make('pricing_summary')
                ->label('Περιγραφή Χρέωσης')
                ->state(fn (Registration $record) =>
                    RegistrationPricingPresenter::from($record)->toTable()['pricing_description']
                ),

            /*
            |--------------------------------------------------------------------------
            | Σχόλια
            |--------------------------------------------------------------------------
            */
            ExportColumn::make('comments')
                ->label('Σχόλια')
                ->enabledByDefault(false),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Η εξαγωγή πρωτοκόλλων ολοκληρώθηκε. '
            . Number::format($export->successful_rows) . ' εγγραφές εξήχθησαν.';

        if ($failed = $export->getFailedRowsCount()) {
            $body .= ' ' . Number::format($failed) . ' εγγραφές απέτυχαν.';
        }

        return $body;
    }
}
