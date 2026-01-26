<?php

namespace App\Filament\Resources\Registrations\Pages;

use App\Filament\Resources\Registrations\RegistrationResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Actions\Action;
use App\Models\Registration;
use App\Models\RegistrationAnalysis;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class CreateRegistration extends CreateRecord
{
    protected static string $resource = RegistrationResource::class;
    protected static null|string $title = 'Δημιουργία Πρωτοκόλλου';

    protected static null|string $breadcrumb = 'Δημιουργία';

    protected function getCreateFormAction(): Action
    {
        return parent::getCreateFormAction()
            ->label('Αποθήκευση') // ✅ custom label for main create button
            ->color('primary')    // optionally change color, icon, etc
            ->icon('heroicon-o-check-circle'); // example icon if you want
    }

    protected function getCreateAnotherFormAction(): Action
    {
        return parent::getCreateAnotherFormAction()
            ->label('Αποθήκευση & Προσθήκη νέου'); // ✅ Your custom Greek label
    }

    public function mount(): void
    {
        parent::mount();

        // Βεβαιώσου ότι το form έχει κάνει fill με τα defaults του schema
        $this->fillForm();

        // Αν ήδη υπάρχει τιμή (old input / redirect), μην την πειράξεις
        if (filled($this->data['registration_number'] ?? null)) {
            return;
        }

        if (($this->data['registration_number_manual'] ?? false) === true) {
            return;
        }

        $date = $this->data['date'] ?? today();
        $year = Carbon::parse($date)->year;

        $payload = [
            'year' => $year,
            'registration_number_system_set' => true,
            'registration_number' => Registration::nextNumberForYear($year),
            'registration_number_system_set' => false,
        ];

        // Γέμισε τα fields στο form state ώστε να εμφανιστούν σίγουρα
        $this->form->fill(array_merge($this->data, $payload));

        // Κράτα και το backing array σε sync
        $this->data = array_merge($this->data, $payload);
    }


    protected function handleRecordCreation(array $data): Registration
    {
        return DB::transaction(function () use ($data) {

            // 1) Create Registration (χωρίς analyses)
            $record = Registration::create(
                Arr::except($data, ['analyses', 'analysis_package_id'])
            );

            $contractSample = $record->contractSample;
            $calculationType = $contractSample?->cost_calculation_type;

            /*
            |--------------------------------------------------------------------------
            | VARIABLE (legacy) → δημιουργία αναλύσεων μία-μία
            |--------------------------------------------------------------------------
            */
            if ($calculationType === \App\Enums\CostCalculationTypeEnum::VARIABLE) {

                $rows = collect($data['analyses'] ?? [])
                    ->filter(fn ($r) => filled($r['lab_analysis_id'] ?? null))
                    ->values();

                foreach ($rows as $r) {
                    RegistrationAnalysis::create([
                        'registration_id' => $record->id,
                        'lab_analysis_id' => (int) $r['lab_analysis_id'],
                        'analysis_name'   => (string) ($r['analysis_name'] ?? ''),
                        'analysis_price'  => (string) ($r['analysis_price'] ?? 0),
                    ]);
                }

                // φορτώνουμε analyses μόνο εδώ
                $record->load('analyses');
            }

            /*
            |--------------------------------------------------------------------------
            | VARIABLE_COUNT → snapshot δεδομένων (χωρίς RegistrationAnalysis)
            |--------------------------------------------------------------------------
            */
            if ($calculationType === \App\Enums\CostCalculationTypeEnum::VARIABLE_COUNT) {

                $record->analyses_count = (int) ($data['analyses_count'] ?? 0);

                $record->analysis_unit_price_snapshot =
                    (float) ($contractSample?->analysis_unit_price ?? 0);
            }

            /*
            |--------------------------------------------------------------------------
            | Τελικός υπολογισμός κόστους (κοινός για όλα τα modes)
            |--------------------------------------------------------------------------
            */
            $record->calculateCost();
            $record->saveQuietly();

            return $record->refresh();
        });
    }
}
