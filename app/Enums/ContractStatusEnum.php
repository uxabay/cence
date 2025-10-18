<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum ContractStatusEnum: string implements HasLabel, HasColor, HasIcon
{
    case Draft = 'draft';
    case Active = 'active';
    case Expired = 'expired';
    case Terminated = 'terminated';

    public function getLabel(): string
    {
        return match ($this) {
            self::Draft => 'Πρόχειρη',
            self::Active => 'Ενεργή',
            self::Expired => 'Ληγμένη',
            self::Terminated => 'Ανενεργή',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Draft => 'gray',
            self::Active => 'success',
            self::Expired => 'warning',
            self::Terminated => 'danger',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::Draft => 'heroicon-o-pencil-square',
            self::Active => 'heroicon-o-bolt',
            self::Expired => 'heroicon-o-clock',
            self::Terminated => 'heroicon-o-x-circle',
        };
    }
}
