<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $title = 'Πίνακας Ελέγχου';

    // 2 columns grid
    public function getColumns(): int | array
    {
        return [
            'md' => 2,
            'lg' => 4,
            'xl' => 4,
        ];
    }

}
