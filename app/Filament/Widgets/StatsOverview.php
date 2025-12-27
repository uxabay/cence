<?php

namespace App\Filament\Widgets;

use App\Models\Contract;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends StatsOverviewWidget
{
    public function getColumns(): int | array
    {
        return [
            'md' => 2,
            'lg' => 4,
            'xl' => 4,
        ];
    }

    protected ?string $heading = 'Επισκόπηση Συμβάσεων';
    protected static ?int $sort = 10;
    protected function getStats(): array
    {
        // -------------------------------------------------------------
        // Load only active contracts (with samples to avoid N+1)
        // -------------------------------------------------------------
        $contracts = Contract::active()
            ->with('samples')
            ->get();

        // -------------------------------------------------------------
        // Basic numbers
        // -------------------------------------------------------------
        $activeContracts = $contracts->count();

        $forecastedAmountTotal = $contracts->sum(function ($c) {
            return $c->forecasted_amount;
        });

        $actualAmountTotal = $contracts->sum(function ($c) {
            return $c->getStats()['actual_amount'];
        });

        $remainingBudget = max($forecastedAmountTotal - $actualAmountTotal, 0);

        $actualSamplesTotal = $contracts->sum(function ($c) {
            return $c->getStats()['actual_samples'];
        });

        $forecastedSamplesTotal = $contracts->sum(function ($c) {
            return $c->getStats()['forecasted_samples'];
        });

        $warningContracts = $contracts->filter->has_warning->count();

        $progressPercentage = $forecastedAmountTotal > 0
            ? round(($actualAmountTotal / $forecastedAmountTotal) * 100, 1)
            : 0;

        // -------------------------------------------------------------
        // Return Stats (4 + 4 layout automatically)
        // -------------------------------------------------------------
        return [

            // 1
            Stat::make('Ενεργές Συμβάσεις', $activeContracts)
                ->icon('heroicon-o-briefcase'),

            // 2
            Stat::make('Προϋπολογισμός', number_format($forecastedAmountTotal, 2).' €')
                ->icon('heroicon-o-banknotes')
                ->color('info'),

            // 3
            Stat::make('Υλοποιηθέντα', number_format($actualAmountTotal, 2).' €')
                ->icon('heroicon-o-check-circle')
                ->color('success'),

            // 4
            Stat::make('Υπόλοιπο', number_format($remainingBudget, 2).' €')
                ->icon('heroicon-o-wallet')
                ->color($remainingBudget <= 0 ? 'danger' : 'gray'),

            // 5
            Stat::make('Δείγματα (Πραγματ.)', number_format($actualSamplesTotal))
                ->icon('heroicon-o-beaker'),

            // 6
            Stat::make('Δείγματα (Προβλεπ.)', number_format($forecastedSamplesTotal))
                ->icon('heroicon-o-queue-list')
                ->color('gray'),

            // 7
            Stat::make('Συμβάσεις σε Warning', $warningContracts)
                ->icon('heroicon-o-exclamation-triangle')
                ->color($warningContracts > 0 ? 'warning' : 'gray'),

            // 8
            Stat::make('Μέσο Progress', $progressPercentage.'%')
                ->icon('heroicon-o-chart-bar')
                ->color(
                    $progressPercentage >= 90 ? 'danger' :
                    ($progressPercentage >= 75 ? 'warning' : 'success')
                ),
        ];
    }
}
