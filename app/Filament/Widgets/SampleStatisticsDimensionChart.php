<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Livewire\Attributes\Reactive;

class SampleStatisticsDimensionChart extends ChartWidget
{
    protected static bool $isDiscovered = false;

    #[Reactive]
    public array $rows = [];

    #[Reactive]
    public bool $compare = true;

    #[Reactive]
    public string $dimension = 'sample';

    protected int|string|array $columnSpan = 'full';

    protected function getMaxHeight(): ?string
    {
        return min(560, max(280, count(array_slice($this->rows, 0, 12)) * 34 + 100)).'px';
    }

    public function updatedRows(): void
    {
        $this->cachedData = null;
    }

    public function updatedCompare(): void
    {
        $this->cachedData = null;
    }

    public function updatedDimension(): void
    {
        $this->cachedData = null;
    }

    public function getHeading(): string
    {
        return match ($this->dimension) {
            'customer_category' => 'Δείγματα ανά κατηγορία πελάτη',
            'customer' => 'Δείγματα ανά πελάτη',
            default => 'Δείγματα ανά κατηγορία δείγματος',
        };
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        $rows = array_slice($this->rows, 0, 12);
        $datasets = [[
            'label' => 'Τρέχουσα περίοδος',
            'data' => array_column($rows, 'received'),
            'backgroundColor' => '#2563eb',
            'borderRadius' => 4,
        ]];

        if ($this->compare) {
            $datasets[] = [
                'label' => 'Περσινή περίοδος',
                'data' => array_column($rows, 'previous_received'),
                'backgroundColor' => '#cbd5e1',
                'borderRadius' => 4,
            ];
        }

        return [
            'labels' => array_column($rows, 'label'),
            'datasets' => $datasets,
        ];
    }

    protected function getOptions(): array
    {
        return [
            'indexAxis' => 'y',
            'responsive' => true,
            'maintainAspectRatio' => true,
            'aspectRatio' => 2.3,
            'plugins' => ['legend' => ['position' => 'bottom']],
            'scales' => [
                'x' => ['beginAtZero' => true, 'ticks' => ['precision' => 0]],
                'y' => ['grid' => ['display' => false]],
            ],
        ];
    }
}
