<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum CostCalculationTypeEnum: string implements HasLabel, HasColor, HasIcon
{
    // Υπάρχουσες τιμές (ΔΕΝ αλλάζουν)
    case FIX = 'fix';
    case VARIABLE = 'variable';

    // Νέα τιμή v1.1.0
    case VARIABLE_COUNT = 'variable_count';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::FIX => 'Σταθερή Τιμή (Fix)',
            self::VARIABLE => 'Μεταβλητή Τιμή – Ανά Ανάλυση',
            self::VARIABLE_COUNT => 'Μεταβλητή Τιμή – Αριθμός Αναλύσεων',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::FIX => 'heroicon-o-currency-euro',
            self::VARIABLE => 'heroicon-o-adjustments-horizontal',
            self::VARIABLE_COUNT => 'heroicon-o-calculator',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::FIX => 'success',
            self::VARIABLE => 'warning',
            self::VARIABLE_COUNT => 'info',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn ($case) => [$case->value => $case->getLabel()])
            ->toArray();
    }
}
