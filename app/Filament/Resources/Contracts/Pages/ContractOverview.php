<?php

namespace App\Filament\Resources\Contracts\Pages;

use App\Filament\Resources\Contracts\ContractResource;
use App\Models\ContractSample;
use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\Indicator;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Actions\Action;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class ContractOverview extends Page implements HasTable
{
    use InteractsWithRecord;
    use Tables\Concerns\InteractsWithTable;

    protected static string $resource = ContractResource::class;
    protected string $view = 'filament.resources.contracts.pages.contract-overview';
    public ?string $from = null;
    public ?string $to = null;

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);
    }

    public function getTitle(): string|Htmlable
    {
        return 'Επισκόπηση Σύμβασης – ' . $this->record->display_title;
    }

    public function getBreadcrumbs(): array
    {
        return [
            route('filament.admin.resources.contracts.index') => 'Συμβάσεις',
            route('filament.admin.resources.contracts.view', $this->record) => $this->record->display_title,
            url()->current() => 'Επισκόπηση',
        ];
    }

    public static function form(Schema $form): Schema
    {
        return $form
            ->components([]);
    }

    /*
    |--------------------------------------------------------------------------
    | Πίνακας (ομαδοποιημένα δεδομένα ανά κατηγορία)
    |--------------------------------------------------------------------------
    */
    public function table(Table $table): Table
    {
        $contract = $this->record;

        return $table
            ->heading('Ανάλυση σύμβασης')
            ->records(function () use ($contract) {
                $from = $this->from;
                $to = $this->to;

                // Ομαδοποιημένα δεδομένα ανά κατηγορία δείγματος
                $categories = $contract->samples()
                    ->with('category')
                    ->get()
                    ->groupBy('category.id')
                    ->map(function ($samples) use ($from, $to) {
                        $category = $samples->first()->category;

                        // Forecasted
                        $forecastedSamples = $samples->sum('net_forecasted_samples');
                        $forecastedAmount  = $samples->sum('net_forecasted_amount');

                        // Actual samples (existing logic)
                        $actualSamples = $samples->sum(fn ($s) => $s->getActualSamples($from, $to));

                        // NEW: Actual amount from registrations.calculated_total
                        $actualAmount = \App\Models\Registration::query()
                            ->whereIn('contract_sample_id', $samples->pluck('id'))
                            ->active()
                            ->betweenDates($from, $to)
                            ->sum('calculated_total');

                        // Progress
                        $progress = $forecastedAmount > 0
                            ? round(($actualAmount / $forecastedAmount) * 100, 1)
                            : 0;

                        return [
                            'category_name'       => $category?->name ?? '(Χωρίς κατηγορία)',
                            'forecasted_samples'  => $forecastedSamples,
                            'forecasted_amount'   => $forecastedAmount,
                            'actual_samples'      => $actualSamples,
                            'actual_amount'       => $actualAmount,
                            'progress'            => $progress,
                        ];
                    })
                    ->values();

                return $categories;
            })
            ->poll('live')
            ->columns([
                Tables\Columns\TextColumn::make('category_name')
                    ->label('Κατηγορία'),

                Tables\Columns\TextColumn::make('forecasted_samples')
                    ->label('Προϋπολογισμένα')
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('actual_samples')
                    ->label('Υλοποιηθέντα')
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('progress')
                    ->label('Ποσοστό')
                    ->badge()
                    ->color(fn (array $record) => $record['progress'] >= 90 ? 'warning' : 'success')
                    ->alignCenter()
                    ->getStateUsing(fn (array $record) => "{$record['progress']}%"),

                Tables\Columns\TextColumn::make('forecasted_amount')
                    ->label('Προϋπολογισμός (€)')
                    ->alignRight()
                    ->formatStateUsing(fn (array $record) => number_format($record['forecasted_amount'], 2, ',', '.')),

                Tables\Columns\TextColumn::make('actual_amount')
                    ->label('Υλοποιηθέν (€)')
                    ->alignRight()
                    ->formatStateUsing(fn (array $record) => number_format($record['actual_amount'], 2, ',', '.')),
            ])
            ->filters([
                Filter::make('period')
                    ->label('Περίοδος')
                    ->schema([
                        DatePicker::make('from_date')
                            ->label('Από')
                            ->native(false)
                            ->default(null)
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                // Αν αλλάξει η "Από", ενημέρωσε το $this->from και έλεγξε την "Έως"
                                $this->from = $state;
                                $to = $get('to_date');
                                if ($to && $state && Carbon::parse($to)->lt(Carbon::parse($state))) {
                                    // Αν η "Έως" είναι πριν την "Από", καθάρισε την
                                    $set('to_date', null);
                                    $this->to = null;
                                }
                            }),

                        DatePicker::make('to_date')
                            ->label('Έως')
                            ->native(false)
                            ->default(null)
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                // Αν αλλάξει η "Έως", ενημέρωσε το $this->to και έλεγξε την "Από"
                                $this->to = $state;
                                $from = $get('from_date');
                                if ($from && $state && Carbon::parse($state)->lt(Carbon::parse($from))) {
                                    // Αν η "Έως" < "Από", διόρθωσε ή καθάρισε
                                    $set('from_date', null);
                                    $this->from = null;
                                }
                            }),
                    ])
                    ->indicateUsing(function (array $data): ?string {
                        $from = $data['from_date'] ?? null;
                        $to = $data['to_date'] ?? null;

                        if (! $from && ! $to) {
                            $this->from = null;
                            $this->to = null;
                            return null;
                        }

                        // Ενημέρωσε τα props στο component
                        $this->from = $from;
                        $this->to = $to;

                        if ($from && $to) {
                            return sprintf(
                                'Από %s έως %s',
                                Carbon::parse($from)->translatedFormat('d/m/Y'),
                                Carbon::parse($to)->translatedFormat('d/m/Y')
                            );
                        }

                        if ($from) {
                            return 'Από ' . Carbon::parse($from)->translatedFormat('d/m/Y');
                        }

                        if ($to) {
                            return 'Έως ' . Carbon::parse($to)->translatedFormat('d/m/Y');
                        }

                        return null;
                    }),
            ])

            ->paginated(false)
            ->striped()
            ->emptyStateHeading('Δεν υπάρχουν κατηγορίες δείγματος')
            ->recordActions([])
            ->toolbarActions([]);
    }

}
