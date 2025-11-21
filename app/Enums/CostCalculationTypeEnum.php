<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum CostCalculationTypeEnum: string implements HasLabel, HasColor, HasIcon
{
    case FIX = 'fix';
    case VARIABLE = 'variable';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::FIX => 'Σταθερή Τιμή (Fix)',
            self::VARIABLE => 'Μεταβλητή Τιμή (Variable)',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::FIX => 'heroicon-o-currency-euro',
            self::VARIABLE => 'heroicon-o-adjustments-horizontal',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::FIX => 'success',
            self::VARIABLE => 'warning',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn ($case) => [$case->value => $case->getLabel()])
            ->toArray();
    }
}
