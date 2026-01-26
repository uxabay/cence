<?php

namespace App\Support\Pricing;

use App\Models\Registration;
use App\Enums\CostCalculationTypeEnum;

final class RegistrationPricingPresenter
{
    private Registration $registration;

    private function __construct(Registration $registration)
    {
        $this->registration = $registration;
    }

    /**
     * Entry point.
     */
    public static function from(Registration $registration): self
    {
        return new self($registration);
    }

    /**
     * Main payload for Infolist / UI.
     * ΠΑΝΤΑ επιστρέφει ίδια δομή.
     */
    public function toInfolist(): array
    {
        if (! $this->hasPricing()) {
            return [
                'has_pricing' => false,
                'pricing_type_label' => '—',
                'pricing_type_color' => 'gray',
                'rows' => [
                    [
                        'label' => 'Κατάσταση',
                        'value' => 'Δεν υπάρχει αντιστοιχισμένη σύμβαση',
                        'emphasis' => true,
                    ],
                ],
            ];
        }

        return [
            'has_pricing' => true,
            'pricing_type_label' => $this->pricingTypeLabel(),
            'pricing_type_color' => $this->pricingTypeColor(),
            'rows' => $this->pricingRows(),
        ];
    }

    /**
     * Compact payload για πίνακες.
     */
    public function toTable(): array
    {
        if (! $this->hasPricing()) {
            return [
                'pricing_label' => '—',
                'pricing_color' => 'gray',
                'pricing_description' => 'Χωρίς σύμβαση',
                'total' => '—',
            ];
        }

        return [
            'pricing_label' => $this->pricingTypeLabel(),
            'pricing_color' => $this->pricingTypeColor(),
            'pricing_description' => $this->pricingSummary(),
            'total' => $this->formatMoney($this->registration->calculated_total),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Guards
    |--------------------------------------------------------------------------
    */
    private function hasPricing(): bool
    {
        return (bool) $this->registration->contractSample;
    }

    /*
    |--------------------------------------------------------------------------
    | Labels & Colors
    |--------------------------------------------------------------------------
    */
    private function pricingTypeLabel(): string
    {
        return match ($this->registration->contractSample->cost_calculation_type) {
            CostCalculationTypeEnum::FIX => 'Σταθερή τιμή',
            CostCalculationTypeEnum::VARIABLE => 'Αναλύσεις',
            CostCalculationTypeEnum::VARIABLE_COUNT => 'Πλήθος αναλύσεων',
        };
    }

    private function pricingTypeColor(): string
    {
        return match ($this->registration->contractSample->cost_calculation_type) {
            CostCalculationTypeEnum::FIX => 'success',
            CostCalculationTypeEnum::VARIABLE,
            CostCalculationTypeEnum::VARIABLE_COUNT => 'warning',
        };
    }

    /*
    |--------------------------------------------------------------------------
    | Rows for Infolist
    |--------------------------------------------------------------------------
    */
    private function pricingRows(): array
    {
        $type = $this->registration->contractSample->cost_calculation_type;

        return match ($type) {
            CostCalculationTypeEnum::FIX => $this->rowsFix(),
            CostCalculationTypeEnum::VARIABLE => $this->rowsVariable(),
            CostCalculationTypeEnum::VARIABLE_COUNT => $this->rowsVariableCount(),
        };
    }

    private function rowsFix(): array
    {
        return [
            ['label' => 'Έγκυρα δείγματα', 'value' => $this->registration->total_samples],
            ['label' => 'Τιμή δείγματος', 'value' => $this->formatMoney($this->registration->calculated_unit_price)],
            [
                'label' => 'Σύνολο χρέωσης',
                'value' => $this->formatMoney($this->registration->calculated_total),
                'emphasis' => true,
            ],
        ];
    }

    private function rowsVariable(): array
    {
        return [
            ['label' => 'Έγκυρα δείγματα', 'value' => $this->registration->total_samples],
            ['label' => 'Αναλύσεις ανά δείγμα', 'value' => $this->registration->analyses_count],
            ['label' => 'Τιμή δείγματος', 'value' => $this->formatMoney($this->registration->calculated_unit_price)],
            [
                'label' => 'Σύνολο χρέωσης',
                'value' => $this->formatMoney($this->registration->calculated_total),
                'emphasis' => true,
            ],
        ];
    }

    private function rowsVariableCount(): array
    {
        return [
            ['label' => 'Έγκυρα δείγματα', 'value' => $this->registration->total_samples],
            ['label' => 'Αναλύσεις ανά δείγμα', 'value' => $this->registration->analyses_count],
            ['label' => 'Τιμή ανάλυσης', 'value' => $this->formatMoney($this->registration->analysis_unit_price_snapshot)],
            ['label' => 'Τιμή δείγματος', 'value' => $this->formatMoney($this->registration->calculated_unit_price)],
            [
                'label' => 'Σύνολο χρέωσης',
                'value' => $this->formatMoney($this->registration->calculated_total),
                'emphasis' => true,
            ],
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Table helpers
    |--------------------------------------------------------------------------
    */
    private function pricingSummary(): string
    {
        return $this->registration->total_samples
            . ' × '
            . $this->formatMoney($this->registration->calculated_unit_price);
    }

    /*
    |--------------------------------------------------------------------------
    | Formatting
    |--------------------------------------------------------------------------
    */
    private function formatMoney(?float $amount): string
    {
        if ($amount === null) {
            return '—';
        }

        return number_format($amount, 2, ',', '.') . ' €';
    }
}
