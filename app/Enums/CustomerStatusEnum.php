<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum CustomerStatusEnum: string implements HasLabel, HasColor, HasIcon
{
    case Active = 'active';
    case Inactive = 'inactive';
    case Archived = 'archived';

    public function getLabel(): string
    {
        return match ($this) {
            self::Active => 'Ενεργός',
            self::Inactive => 'Ανενεργός',
            self::Archived => 'Αρχειοθετημένος',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Active => 'success',
            self::Inactive => 'gray',
            self::Archived => 'warning',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Active => 'heroicon-o-check-circle',
            self::Inactive => 'heroicon-o-x-circle',
            self::Archived => 'heroicon-o-archive-box',
        };
    }
}
