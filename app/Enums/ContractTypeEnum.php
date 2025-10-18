<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum ContractTypeEnum: string implements HasLabel, HasColor, HasIcon
{
    case Programmatiki = 'programmatiki';
    case Tropopoiitiki = 'tropopoiitiki';
    case Symplirwmatiki = 'symplirwmatiki';

    public function getLabel(): string
    {
        return match ($this) {
            self::Programmatiki => 'Προγραμματική',
            self::Tropopoiitiki => 'Τροποποιητική',
            self::Symplirwmatiki => 'Συμπληρωματική',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Programmatiki => 'primary',
            self::Tropopoiitiki => 'warning',
            self::Symplirwmatiki => 'info',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::Programmatiki => 'heroicon-o-document-text',
            self::Tropopoiitiki => 'heroicon-o-arrow-path',
            self::Symplirwmatiki => 'heroicon-o-plus-circle',
        };
    }
}
