<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Livewire\Attributes\Reactive;

class SampleStatisticsContractSplitWidget extends StatsOverviewWidget
{
    protected static bool $isDiscovered = false;

    #[Reactive]
    public array $contractSplit = [];

    protected int|string|array $columnSpan = 'full';

    protected ?string $heading = 'Παραληφθέντα με και χωρίς σύμβαση';

    public function updatedContractSplit(): void
    {
        $this->cachedStats = null;
    }

    protected function getColumns(): int|array|null
    {
        return ['default' => 1, 'sm' => 2];
    }

    protected function getStats(): array
    {
        return [
            Stat::make('Με σύμβαση', number_format($this->contractSplit['with_contract'] ?? 0, 0, ',', '.'))
                ->icon('heroicon-o-clipboard-document-check')
                ->color('success'),
            Stat::make('Χωρίς σύμβαση', number_format($this->contractSplit['without_contract'] ?? 0, 0, ',', '.'))
                ->icon('heroicon-o-inbox')
                ->color('gray'),
        ];
    }
}
