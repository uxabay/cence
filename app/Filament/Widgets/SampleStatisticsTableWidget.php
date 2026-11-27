<?php

namespace App\Filament\Widgets;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Livewire\Attributes\Reactive;

class SampleStatisticsTableWidget extends TableWidget
{
    protected static bool $isDiscovered = false;

    #[Reactive]
    public array $rows = [];

    #[Reactive]
    public bool $compare = true;

    #[Reactive]
    public string $dimension = 'sample';

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading($this->getHeading())
            ->records(fn (): array => $this->rows)
            ->columns([
                TextColumn::make('label')
                    ->label($this->getDimensionLabel())
                    ->searchable()
                    ->sortable()
                    ->wrap(),
                TextColumn::make('received')
                    ->label('Παραληφθέντα')
                    ->numeric(decimalPlaces: 0, decimalSeparator: ',', thousandsSeparator: '.')
                    ->alignRight()
                    ->sortable(),
                TextColumn::make('valid')
                    ->label('Έγκυρα')
                    ->numeric(decimalPlaces: 0, decimalSeparator: ',', thousandsSeparator: '.')
                    ->alignRight()
                    ->sortable(),
                TextColumn::make('invalid')
                    ->label('Μη έγκυρα')
                    ->numeric(decimalPlaces: 0, decimalSeparator: ',', thousandsSeparator: '.')
                    ->alignRight()
                    ->sortable(),
                ...($this->compare ? [
                    TextColumn::make('previous_received')
                        ->label('Πέρυσι')
                        ->numeric(decimalPlaces: 0, decimalSeparator: ',', thousandsSeparator: '.')
                        ->alignRight(),
                    TextColumn::make('change')
                        ->label('Μεταβολή')
                        ->alignRight()
                        ->formatStateUsing(function ($state, array $record): string {
                            if ($record['previous_received'] === 0 && $record['received'] > 0) {
                                return 'Δεν υπήρχαν δείγματα πέρυσι';
                            }

                            if ($state === null) {
                                return '—';
                            }

                            $change = ($state > 0 ? '+' : '').number_format((int) $state, 0, ',', '.');

                            return $record['change_percent'] === null
                                ? $change
                                : $change.' ('.($record['change_percent'] > 0 ? '+' : '')
                                    .number_format((float) $record['change_percent'], 1, ',', '.').'%)';
                        }),
                ] : []),
            ])
            ->defaultSort('received', 'desc')
            ->paginated([10, 25, 50])
            ->defaultPaginationPageOption(10)
            ->emptyStateHeading('Δεν βρέθηκαν δείγματα για τα επιλεγμένα φίλτρα.')
            ->recordActions([])
            ->toolbarActions([]);
    }

    private function getHeading(): string
    {
        return match ($this->dimension) {
            'customer_category' => 'Ανά κατηγορία πελάτη',
            'customer' => 'Πελάτες με τις περισσότερες παραλαβές',
            default => 'Ανά κατηγορία δείγματος',
        };
    }

    private function getDimensionLabel(): string
    {
        return match ($this->dimension) {
            'customer_category' => 'Κατηγορία πελάτη',
            'customer' => 'Πελάτης',
            default => 'Κατηγορία δείγματος',
        };
    }
}
