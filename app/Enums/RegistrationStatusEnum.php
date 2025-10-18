<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum RegistrationStatusEnum: string implements HasLabel, HasColor, HasIcon
{
    case Draft = 'draft';
    case Confirmed = 'confirmed';
    case Archived = 'archived';
    case Cancelled = 'cancelled';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Draft => 'Πρόχειρο',
            self::Confirmed => 'Οριστικοποιημένο',
            self::Archived => 'Αρχειοθετημένο',
            self::Cancelled => 'Ακυρωμένο',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Draft => 'gray',
            self::Confirmed => 'success',
            self::Archived => 'info',
            self::Cancelled => 'danger',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Draft => 'heroicon-m-pencil-square',
            self::Confirmed => 'heroicon-m-check-badge',
            self::Archived => 'heroicon-m-archive-box',
            self::Cancelled => 'heroicon-m-x-circle',
        };
    }
}
