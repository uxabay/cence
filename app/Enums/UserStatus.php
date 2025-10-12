<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;
use Filament\Support\Contracts\HasColor;

enum UserStatus: string implements HasLabel, HasColor
{
    case ACTIVE = 'active';
    case ARCHIVED = 'archived';
    case SUSPENDED = 'suspended';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::ACTIVE => 'Ενεργός',
            self::ARCHIVED => 'Ανενεργός',
            self::SUSPENDED => 'Ανεσταλμένος',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::ACTIVE => 'success',
            self::ARCHIVED => 'gray',
            self::SUSPENDED => 'danger',
        };
    }
}
