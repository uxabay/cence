<?php

namespace App\Support\Contracts;

use App\Models\Contract;
use App\Models\ContractSample;
use App\Models\Registration;
use Carbon\Carbon;

class ContractOverviewData
{
    public function build(Contract $contract, ?string $from = null, ?string $to = null): array
    {
        $categories = $contract->samples()
            ->with('category')
            ->get()
            ->groupBy('category.id')
            ->map(function ($samples) use ($from, $to): array {
                $category = $samples->first()->category;
                $forecastedSamples = (int) $samples->sum('net_forecasted_samples');
                $forecastedAmount = (float) $samples->sum('net_forecasted_amount');
                $actualSamples = (int) $samples->sum(
                    fn (ContractSample $sample) => $sample->getActualSamples($from, $to)
                );
                $actualAmount = (float) Registration::query()
                    ->whereIn('contract_sample_id', $samples->pluck('id'))
                    ->active()
                    ->betweenDates($from, $to)
                    ->sum('calculated_total');

                return $this->makeRow(
                    $category?->name ?? '(Χωρίς κατηγορία)',
                    $forecastedSamples,
                    $actualSamples,
                    $forecastedAmount,
                    $actualAmount,
                );
            })
            ->values();

        $summary = $this->makeRow(
            'Σύνολο',
            (int) $categories->sum('forecasted_samples'),
            (int) $categories->sum('actual_samples'),
            (float) $categories->sum('forecasted_amount'),
            (float) $categories->sum('actual_amount'),
            true,
        );

        return [
            'categories' => $categories->all(),
            'summary' => $summary,
            'table_rows' => [...$categories->all(), $summary],
        ];
    }

    public function periodLabel(?string $from, ?string $to): string
    {
        if ($from && $to) {
            return 'Πρωτόκολλα από '.Carbon::parse($from)->translatedFormat('d/m/Y')
                .' έως '.Carbon::parse($to)->translatedFormat('d/m/Y');
        }

        if ($from) {
            return 'Πρωτόκολλα από '.Carbon::parse($from)->translatedFormat('d/m/Y');
        }

        if ($to) {
            return 'Πρωτόκολλα έως '.Carbon::parse($to)->translatedFormat('d/m/Y');
        }

        return 'Πρωτόκολλα για όλη τη διάρκεια της σύμβασης';
    }

    private function makeRow(string $name, int $forecastedSamples, int $actualSamples, float $forecastedAmount, float $actualAmount, bool $isTotal = false): array
    {
        return [
            'category_name' => $name,
            'forecasted_samples' => $forecastedSamples,
            'actual_samples' => $actualSamples,
            'samples_balance' => $this->formatBalance($forecastedSamples, $actualSamples, false),
            'forecasted_amount' => $forecastedAmount,
            'actual_amount' => $actualAmount,
            'amount_balance' => $this->formatBalance($forecastedAmount, $actualAmount, true),
            'progress' => $forecastedAmount > 0 ? round(($actualAmount / $forecastedAmount) * 100, 1) : 0.0,
            'is_total' => $isTotal,
        ];
    }

    private function formatBalance(float|int $forecasted, float|int $actual, bool $money): string
    {
        $difference = $forecasted - $actual;

        if (abs($difference) < ($money ? 0.005 : 1)) {
            return ($forecasted === 0.0 || $forecasted === 0) && ($actual === 0.0 || $actual === 0)
                ? '—'
                : 'Ισοσκελισμένο';
        }

        $value = number_format(abs($difference), $money ? 2 : 0, ',', '.');
        $unit = $money ? ' €' : ' δείγματα';

        return $difference > 0 ? "Υπόλοιπο {$value}{$unit}" : "Υπέρβαση {$value}{$unit}";
    }
}
