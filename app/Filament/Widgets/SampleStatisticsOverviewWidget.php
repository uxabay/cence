<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Livewire\Attributes\Reactive;

class SampleStatisticsOverviewWidget extends StatsOverviewWidget
{
    protected static bool $isDiscovered = false;

    #[Reactive]
    public array $summary = [];

    #[Reactive]
    public bool $compare = true;

    #[Reactive]
    public string $comparisonLabel = '';

    protected int|string|array $columnSpan = 'full';

    protected ?string $heading = 'Σύνοψη δειγμάτων';

    public function updatedSummary(): void
    {
        $this->cachedStats = null;
    }

    public function updatedCompare(): void
    {
        $this->cachedStats = null;
    }

    public function updatedComparisonLabel(): void
    {
        $this->cachedStats = null;
    }

    protected function getColumns(): int|array|null
    {
        return ['default' => 1, 'sm' => 2, 'xl' => 4];
    }

    protected function getStats(): array
    {
        $metrics = [
            'received' => ['Παραληφθέντα δείγματα', 'heroicon-o-beaker'],
            'valid' => ['Έγκυρα δείγματα', 'heroicon-o-check-circle'],
            'invalid' => ['Μη έγκυρα δείγματα', 'heroicon-o-exclamation-circle'],
            'invalid_rate' => ['Ποσοστό μη έγκυρων', 'heroicon-o-chart-pie'],
        ];

        return collect($metrics)->map(function (array $definition, string $key): Stat {
            $metric = $this->summary[$key] ?? ['current' => 0, 'previous' => null, 'change' => null, 'change_percent' => null];
            $isRate = $key === 'invalid_rate';
            $value = number_format((float) $metric['current'], $isRate ? 1 : 0, ',', '.').($isRate ? '%' : '');
            $stat = Stat::make($definition[0], $value)->icon($definition[1]);

            if (! $this->compare) {
                return $stat;
            }

            $previous = number_format((float) $metric['previous'], $isRate ? 1 : 0, ',', '.').($isRate ? '%' : '');
            $description = "Πέρυσι ({$this->comparisonLabel}): {$previous}";

            if ($isRate) {
                $description .= ' · '.($metric['change'] > 0 ? '+' : '')
                    .number_format((float) $metric['change'], 1, ',', '.').' π.μ.';
            } elseif ($metric['previous'] === 0 && $metric['current'] > 0) {
                $description .= ' · νέα βάση σύγκρισης';
            } elseif ($metric['change_percent'] !== null) {
                $description .= ' · '.($metric['change'] > 0 ? '+' : '')
                    .number_format((float) $metric['change'], 0, ',', '.').' ('
                    .($metric['change'] > 0 ? '+' : '')
                    .number_format((float) $metric['change_percent'], 1, ',', '.').'%)';
            }

            return $stat->description($description);
        })->all();
    }
}
