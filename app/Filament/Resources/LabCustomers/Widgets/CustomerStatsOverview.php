<?php

namespace App\Filament\Resources\LabCustomers\Widgets;

use App\Models\LabCustomer;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CustomerStatsOverview extends StatsOverviewWidget
{
    use InteractsWithPageTable;

    protected ?string $pollingInterval = '10s';
    protected int|string|array $columnSpan = 'full';

    protected function getTablePage(): string
    {
        return \App\Filament\Resources\LabCustomers\Pages\ListLabCustomers::class;
    }

    protected function getStats(): array
    {
        $query = $this->getPageTableQuery() ?? LabCustomer::query();

        $activeCount   = (clone $query)->active()->count();
        $inactiveCount = (clone $query)->inactive()->count();
        $archivedCount = (clone $query)->archived()->count();
        $totalCount    = $query->count();

        return [
            Stat::make('Ενεργοί Πελάτες', $activeCount)
                //->description('Φιλτραρισμένοι ενεργοί πελάτες')
                ->descriptionIcon('heroicon-o-check-circle')
                ->color('success'),

            Stat::make('Ανενεργοί Πελάτες', $inactiveCount)
                //->description('Φιλτραρισμένοι ανενεργοί πελάτες')
                ->descriptionIcon('heroicon-o-x-circle')
                ->color('warning'),

            Stat::make('Αρχειοθετημένοι', $archivedCount)
                //->description('Φιλτραρισμένοι αρχειοθετημένοι πελάτες')
                ->descriptionIcon('heroicon-o-archive-box')
                ->color('gray'),

            Stat::make('Σύνολο', $totalCount)
                //->description('Σύνολο εγγραφών στο φιλτραρισμένο query')
                ->descriptionIcon('heroicon-o-user-group')
                ->color('primary'),
        ];
    }
}
