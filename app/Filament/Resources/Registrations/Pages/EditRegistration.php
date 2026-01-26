<?php

namespace App\Filament\Resources\Registrations\Pages;

use App\Filament\Resources\Registrations\RegistrationResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use App\Models\Registration;
use App\Models\RegistrationAnalysis;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class EditRegistration extends EditRecord
{
    protected static string $resource = RegistrationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        $record = $this->record;
        $type = $record->contractSample?->cost_calculation_type;

        if ($type === \App\Enums\CostCalculationTypeEnum::VARIABLE) {
            $record->load('analyses');
        }

        $record->calculateCost();
        $record->saveQuietly();
    }

    protected function handleRecordUpdate($record, array $data): Registration
    {
        return DB::transaction(function () use ($record, $data) {

            // 1) Update parent (χωρίς analyses)
            $record->update(
                Arr::except($data, ['analyses', 'analysis_package_id'])
            );

            $contractSample = $record->contractSample;
            $type = $contractSample?->cost_calculation_type;

            /*
            |--------------------------------------------------------------------------
            | VARIABLE (legacy) → πλήρες sync αναλύσεων
            |--------------------------------------------------------------------------
            */
            if ($type === \App\Enums\CostCalculationTypeEnum::VARIABLE) {

                $rows = collect($data['analyses'] ?? [])
                    ->filter(fn ($r) => filled($r['lab_analysis_id'] ?? null))
                    ->values();

                $keepIds = $rows->pluck('lab_analysis_id')->unique()->values();

                foreach ($rows as $r) {
                    $labAnalysisId = (int) $r['lab_analysis_id'];

                    $payload = [
                        'analysis_name'  => (string) ($r['analysis_name'] ?? ''),
                        'analysis_price' => (float)  ($r['analysis_price'] ?? 0),
                    ];

                    $existing = RegistrationAnalysis::withTrashed()
                        ->where('registration_id', $record->id)
                        ->where('lab_analysis_id', $labAnalysisId)
                        ->first();

                    if ($existing) {
                        if ($existing->trashed()) {
                            $existing->restore();
                        }
                        $existing->fill($payload)->save();
                    } else {
                        RegistrationAnalysis::create([
                            'registration_id' => $record->id,
                            'lab_analysis_id' => $labAnalysisId,
                            ...$payload,
                        ]);
                    }
                }

                // soft-delete όσα αφαιρέθηκαν
                RegistrationAnalysis::query()
                    ->where('registration_id', $record->id)
                    ->whereNotIn('lab_analysis_id', $keepIds)
                    ->delete();

                // reload μόνο εδώ
                $record->load('analyses');
            }

            /*
            |--------------------------------------------------------------------------
            | VARIABLE_COUNT → snapshot fields μόνο
            |--------------------------------------------------------------------------
            */
            if ($type === \App\Enums\CostCalculationTypeEnum::VARIABLE_COUNT) {

                $record->analyses_count = (int) ($data['analyses_count'] ?? 0);

                $record->analysis_unit_price_snapshot =
                    (float) ($contractSample?->analysis_unit_price ?? 0);
            }

            // FIX → τίποτα extra

            // Τελικός υπολογισμός
            $record->calculateCost();
            $record->saveQuietly();

            return $record->refresh();
        });
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        /** @var \App\Models\Registration $record */
        $record = $this->record;

        $data['analyses'] = RegistrationAnalysis::query()
            ->where('registration_id', $record->id)
            ->orderBy('id')
            ->get()
            ->map(fn (RegistrationAnalysis $ra) => [
                // Βάλε id για να κρατήσεις σταθερότητα στο UI, παρότι εμείς κάνουμε sync στο save
                'id'            => $ra->id,
                'lab_analysis_id' => $ra->lab_analysis_id,
                'analysis_name'   => $ra->analysis_name,
                'analysis_price'  => (string) $ra->analysis_price,
            ])
            ->toArray();

        return $data;
    }

}
