<?php

namespace App\Filament\Resources\Contracts\Pages;

use App\Filament\Resources\Contracts\ContractResource;
use App\Support\Contracts\ContractOverviewData;
use Carbon\Carbon;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;

class ContractOverview extends Page implements HasTable
{
    use InteractsWithRecord;
    use Tables\Concerns\InteractsWithTable;

    protected static string $resource = ContractResource::class;

    protected string $view = 'filament.resources.contracts.pages.contract-overview';

    public ?string $from = null;

    public ?string $to = null;

    protected ?array $overviewData = null;

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);
        $this->setPeriodPreset('contract_to_date');
    }

    public function getTitle(): string|Htmlable
    {
        return 'Επισκόπηση Σύμβασης – '.$this->record->display_title;
    }

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            EditAction::make(),
        ];
    }

    public static function form(Schema $form): Schema
    {
        return $form->components([]);
    }

    /**
     * Provides one shared set of totals for the summary and the category table.
     * Forecast values are for the full contract; actuals respect the date filter.
     */
    public function getOverviewData(): array
    {
        if ($this->overviewData !== null) {
            return $this->overviewData;
        }

        return $this->overviewData = app(ContractOverviewData::class)->build($this->record, $this->from, $this->to);
    }

    public function getPeriodLabel(): string
    {
        return app(ContractOverviewData::class)->periodLabel($this->from, $this->to);
    }

    public function updatedFrom(): void
    {
        $this->from = $this->normalizeDate($this->from, $this->defaultFrom());
        $this->to = $this->normalizeDate($this->to, today()->toDateString());

        if (Carbon::parse($this->to)->lt(Carbon::parse($this->from))) {
            $this->to = $this->from;
        }

        $this->overviewData = null;
    }

    public function updatedTo(): void
    {
        $this->from = $this->normalizeDate($this->from, $this->defaultFrom());
        $this->to = $this->normalizeDate($this->to, today()->toDateString());

        if (Carbon::parse($this->to)->lt(Carbon::parse($this->from))) {
            $this->from = $this->to;
        }

        $this->overviewData = null;
    }

    public function clearPeriod(): void
    {
        $this->setPeriodPreset('contract_to_date');
    }

    public function setPeriodPreset(string $preset): void
    {
        $today = today();
        $currentQuarterStart = $today->copy()->startOfQuarter();

        [$from, $to] = match ($preset) {
            'current_quarter' => [$currentQuarterStart, $today],
            'previous_quarter' => [
                $currentQuarterStart->copy()->subQuarter()->startOfQuarter(),
                $currentQuarterStart->copy()->subDay(),
            ],
            'last_four_quarters' => [$currentQuarterStart->copy()->subQuarters(3), $today],
            'year_to_date' => [$today->copy()->startOfYear(), $today],
            default => [Carbon::parse($this->record->date_start ?? $today), $today],
        };

        $this->from = $from->toDateString();
        $this->to = $to->toDateString();
        $this->overviewData = null;
    }

    private function defaultFrom(): string
    {
        return Carbon::parse($this->record->date_start ?? today())->toDateString();
    }

    private function normalizeDate(?string $date, string $fallback): string
    {
        $parsed = $date ? \DateTimeImmutable::createFromFormat('!Y-m-d', $date) : false;

        return $parsed && $parsed->format('Y-m-d') === $date
            ? $date
            : $fallback;
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading('Ανάλυση ανά κατηγορία σύμβασης')
            ->records(fn () => $this->getOverviewData()['table_rows'])
            ->columns([
                Tables\Columns\TextColumn::make('category_name')
                    ->label('Κατηγορία')
                    ->wrap()
                    ->weight(fn (array $record) => $record['is_total'] ? 'bold' : 'regular'),

                Tables\Columns\TextColumn::make('forecasted_samples')
                    ->label('Δείγματα')
                    ->alignRight()
                    ->formatStateUsing(fn ($state) => number_format((int) $state, 0, ',', '.'))
                    ->description(fn (array $record) => 'Υλοπ. '
                        .number_format($record['actual_samples'], 0, ',', '.')
                        .' · '.str_replace(' δείγματα', '', $record['samples_balance'])),

                Tables\Columns\TextColumn::make('forecasted_amount')
                    ->label('Προϋπολογισμός')
                    ->alignRight()
                    ->formatStateUsing(fn ($state) => number_format((float) $state, 2, ',', '.').' €')
                    ->description(fn (array $record) => 'Υλοπ. '
                        .number_format($record['actual_amount'], 2, ',', '.').' €'),

                Tables\Columns\TextColumn::make('amount_balance')
                    ->label('Υπόλοιπο / υπέρβαση')
                    ->wrap()
                    ->description(fn (array $record) => 'Δείγματα: '
                        .str_replace(' δείγματα', '', $record['samples_balance']))
                    ->alignRight(),

                Tables\Columns\TextColumn::make('progress')
                    ->label('Υλοποίηση')
                    ->badge()
                    ->color(fn (array $record) => $record['progress'] >= 100
                        ? 'danger'
                        : ($record['progress'] >= 90 ? 'warning' : 'success'))
                    ->alignRight()
                    ->formatStateUsing(fn ($state) => number_format((float) $state, 1, ',', '.').'%'),
            ])
            ->paginated(false)
            ->striped()
            ->emptyStateHeading('Δεν υπάρχουν κατηγορίες δείγματος')
            ->recordActions([])
            ->toolbarActions([]);
    }
}
