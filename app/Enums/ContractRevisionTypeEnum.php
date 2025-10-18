<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum ContractRevisionTypeEnum: string implements HasLabel, HasColor, HasIcon
{
    case Financial = 'financial';
    case Time = 'time';
    case Technical = 'technical';
    case Mixed = 'mixed';

    public function getLabel(): string
    {
        return match ($this) {
            self::Financial => 'Οικονομική',
            self::Time => 'Χρονική',
            self::Technical => 'Τεχνική',
            self::Mixed => 'Μικτή',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Financial => 'success',
            self::Time => 'info',
            self::Technical => 'warning',
            self::Mixed => 'purple',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::Financial => 'heroicon-o-banknotes',
            self::Time => 'heroicon-o-clock',
            self::Technical => 'heroicon-o-wrench-screwdriver',
            self::Mixed => 'heroicon-o-squares-2x2',
        };
    }
}
