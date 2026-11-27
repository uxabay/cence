<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Livewire\Attributes\Reactive;

class SampleStatisticsTrendChart extends ChartWidget
{
    protected static bool $isDiscovered = false;

    #[Reactive]
    public array $trend = [];

    #[Reactive]
    public bool $compare = true;

    #[Reactive]
    public string $periodDescription = '';

    protected ?string $heading = 'Μηνιαία εξέλιξη παραλαβών';

    protected ?string $maxHeight = '320px';

    protected int|string|array $columnSpan = 'full';

    public function updatedTrend(): void
    {
        $this->cachedData = null;
    }

    public function updatedCompare(): void
    {
        $this->cachedData = null;
    }

    protected function getType(): string
    {
        return 'bar';
    }

    public function getDescription(): ?string
    {
        return $this->periodDescription;
    }

    protected function getData(): array
    {
        $months = $this->trend['months'] ?? [];
        $datasets = [[
            'label' => 'Τρέχουσα περίοδος',
            'data' => array_column($months, 'received'),
            'backgroundColor' => '#2563eb',
            'borderRadius' => 4,
        ]];

        if ($this->compare) {
            $datasets[] = [
                'label' => 'Περσινή περίοδος',
                'data' => array_column($months, 'previous_received'),
                'backgroundColor' => '#cbd5e1',
                'borderRadius' => 4,
            ];
        }

        return [
            'labels' => array_column($months, 'label'),
            'datasets' => $datasets,
        ];
    }

    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'maintainAspectRatio' => true,
            'aspectRatio' => 2.7,
            'plugins' => ['legend' => ['position' => 'bottom']],
            'scales' => [
                'x' => ['grid' => ['display' => false]],
                'y' => ['beginAtZero' => true, 'ticks' => ['precision' => 0]],
            ],
        ];
    }
}
