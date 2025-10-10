<?php

namespace App\Filament\Support;

use Filament\Support\Contracts\HasLabel;

enum NavigationGroupsNames: string implements HasLabel
{
    case CONTRACTS  = 'Συμβάσεις';
    case LABORATORY = 'Εργαστήριο';
    case SYSTEM     = 'Σύστημα';
    case REPORTS    = 'Αναφορές';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::CONTRACTS => 'Συμβάσεις',
            self::LABORATORY => 'Εργαστήριο',
            self::SYSTEM => 'Σύστημα',
            self::REPORTS => 'Αναφορές',
        };
    }
}
