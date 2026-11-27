<?php

namespace App\Support\Statistics;

use App\Enums\RecordStatusEnum;
use App\Models\Registration;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class SampleStatisticsService
{
    public function build(array $filters): array
    {
        $from = CarbonImmutable::parse($filters['from'])->startOfDay();
        $to = CarbonImmutable::parse($filters['to'])->endOfDay();
        $previousFrom = $from->subYearNoOverflow();
        $previousTo = $to->subYearNoOverflow();
        $compare = (bool) ($filters['compare'] ?? true);

        $summary = $this->summary($filters, $from, $to);
        $previousSummary = $compare ? $this->summary($filters, $previousFrom, $previousTo) : null;
        $comparisonAvailable = $compare && (($previousSummary['received'] ?? 0) > 0);

        return [
            'from' => $from,
            'to' => $to,
            'previous_from' => $previousFrom,
            'previous_to' => $previousTo,
            'compare' => $comparisonAvailable,
            'summary' => $this->compareMetricSets($summary, $previousSummary),
            'undated' => $this->undatedSummary($filters),
            'contract_split' => $this->contractSplit($filters, $from, $to),
            'sample_categories' => $this->dimensionRows(
                $filters,
                $from,
                $to,
                'sample',
                $compare ? $previousFrom : null,
                $compare ? $previousTo : null,
            ),
            'customer_categories' => $this->dimensionRows(
                $filters,
                $from,
                $to,
                'customer_category',
                $compare ? $previousFrom : null,
                $compare ? $previousTo : null,
            ),
            'top_customers' => $this->dimensionRows(
                $filters,
                $from,
                $to,
                'customer',
                $compare ? $previousFrom : null,
                $compare ? $previousTo : null,
                10,
            ),
            'monthly_trend' => $this->monthlyTrend($filters, $from, $to, $previousFrom, $previousTo, $comparisonAvailable),
        ];
    }

    private function summary(array $filters, CarbonImmutable $from, CarbonImmutable $to): array
    {
        $totals = $this->registrations($filters, $from, $to)
            ->selectRaw('COALESCE(SUM(registrations.num_samples_received), 0) as received')
            ->selectRaw('COALESCE(SUM(registrations.total_samples), 0) as valid')
            ->selectRaw('COALESCE(SUM(registrations.not_valid_samples), 0) as invalid')
            ->first();

        $received = (int) $totals->received;
        $invalid = (int) $totals->invalid;

        return [
            'received' => $received,
            'valid' => (int) $totals->valid,
            'invalid' => $invalid,
            'invalid_rate' => $received > 0 ? round(($invalid / $received) * 100, 1) : 0.0,
        ];
    }

    private function contractSplit(array $filters, CarbonImmutable $from, CarbonImmutable $to): array
    {
        return $this->registrations($filters, $from, $to)
            ->selectRaw("CASE WHEN registrations.contract_id IS NOT NULL OR EXISTS (SELECT 1 FROM contract_samples AS linked_contract_sample WHERE linked_contract_sample.id = registrations.contract_sample_id AND linked_contract_sample.deleted_at IS NULL) THEN 'with_contract' ELSE 'without_contract' END as segment")
            ->selectRaw('COALESCE(SUM(registrations.num_samples_received), 0) as received')
            ->groupBy('segment')
            ->get()
            ->mapWithKeys(fn ($row) => [$row->segment => (int) $row->received])
            ->all() + ['with_contract' => 0, 'without_contract' => 0];
    }

    private function undatedSummary(array $filters): array
    {
        $totals = $this->registrations($filters)
            ->whereNull('registrations.date')
            ->selectRaw('COUNT(*) as protocols')
            ->selectRaw('COALESCE(SUM(registrations.num_samples_received), 0) as received')
            ->first();

        return [
            'protocols' => (int) $totals->protocols,
            'received' => (int) $totals->received,
        ];
    }

    private function dimensionRows(
        array $filters,
        CarbonImmutable $from,
        CarbonImmutable $to,
        string $dimension,
        ?CarbonImmutable $previousFrom = null,
        ?CarbonImmutable $previousTo = null,
        ?int $limit = null,
    ): array {
        $current = $this->groupedRows($filters, $from, $to, $dimension, $limit)->keyBy('group_id');
        $comparisonEnabled = $previousFrom !== null && $previousTo !== null;
        $previous = $comparisonEnabled
            ? $this->groupedRows($filters, $previousFrom, $previousTo, $dimension, $limit)->keyBy('group_id')
            : collect();

        return $current->keys()
            ->merge($previous->keys())
            ->unique()
            ->map(function ($groupId) use ($current, $previous, $comparisonEnabled) {
                $now = $current->get($groupId);
                $before = $previous->get($groupId);
                $currentReceived = (int) ($now->received ?? 0);
                $previousReceived = $comparisonEnabled ? (int) ($before->received ?? 0) : null;

                return [
                    'group_id' => $groupId,
                    'label' => $now->label ?? $before->label,
                    'received' => $currentReceived,
                    'valid' => (int) ($now->valid ?? 0),
                    'invalid' => (int) ($now->invalid ?? 0),
                    'previous_received' => $previousReceived,
                    'change' => $previousReceived === null ? null : $currentReceived - $previousReceived,
                    'change_percent' => $previousReceived === null || $previousReceived === 0
                        ? null
                        : round((($currentReceived - $previousReceived) / $previousReceived) * 100, 1),
                ];
            })
            ->sortByDesc('received')
            ->values()
            ->all();
    }

    private function groupedRows(array $filters, CarbonImmutable $from, CarbonImmutable $to, string $dimension, ?int $limit): Collection
    {
        $query = $this->registrations($filters, $from, $to);

        if ($dimension === 'sample') {
            $query->leftJoin('lab_sample_categories as sample_category', 'sample_category.id', '=', 'registrations.lab_sample_category_id')
                ->selectRaw('COALESCE(sample_category.id, 0) as group_id')
                ->selectRaw('COALESCE(sample_category.name, ?) as label', ['Χωρίς κατηγορία δείγματος'])
                ->groupBy('sample_category.id', 'sample_category.name');
        } elseif ($dimension === 'customer_category') {
            $query->leftJoin('lab_customers as sample_customer', 'sample_customer.id', '=', 'registrations.customer_id')
                ->leftJoin('customer_categories as sample_customer_category', 'sample_customer_category.id', '=', 'sample_customer.customer_category_id')
                ->selectRaw('COALESCE(sample_customer_category.id, 0) as group_id')
                ->selectRaw('COALESCE(sample_customer_category.name, ?) as label', ['Χωρίς κατηγορία πελάτη'])
                ->groupBy('sample_customer_category.id', 'sample_customer_category.name');
        } else {
            $query->leftJoin('lab_customers as sample_customer', 'sample_customer.id', '=', 'registrations.customer_id')
                ->selectRaw('COALESCE(sample_customer.id, 0) as group_id')
                ->selectRaw('COALESCE(sample_customer.name, ?) as label', ['Χωρίς πελάτη'])
                ->groupBy('sample_customer.id', 'sample_customer.name');
        }

        $query->selectRaw('COALESCE(SUM(registrations.num_samples_received), 0) as received')
            ->selectRaw('COALESCE(SUM(registrations.total_samples), 0) as valid')
            ->selectRaw('COALESCE(SUM(registrations.not_valid_samples), 0) as invalid')
            ->orderByDesc('received');

        if ($limit !== null) {
            $query->limit($limit);
        }

        return $query->get();
    }

    private function monthlyTrend(
        array $filters,
        CarbonImmutable $from,
        CarbonImmutable $to,
        CarbonImmutable $previousFrom,
        CarbonImmutable $previousTo,
        bool $compare,
    ): array {
        $currentByMonth = $this->dailyTotals($filters, $from, $to);
        $previousByMonth = $compare ? $this->dailyTotals($filters, $previousFrom, $previousTo) : [];
        $months = [];
        $cursor = $from->startOfMonth();

        while ($cursor <= $to) {
            $previousMonth = $cursor->subYearNoOverflow();
            $currentKey = $cursor->format('Y-m');
            $previousKey = $previousMonth->format('Y-m');
            $months[] = [
                'label' => $cursor->format('m/Y'),
                'received' => (int) ($currentByMonth[$currentKey] ?? 0),
                'previous_received' => $compare ? (int) ($previousByMonth[$previousKey] ?? 0) : null,
            ];
            $cursor = $cursor->addMonthNoOverflow();
        }

        $max = max(1, ...array_column($months, 'received'), ...array_filter(array_column($months, 'previous_received'), fn ($value) => $value !== null));

        return [
            'months' => array_map(function ($month) use ($max) {
                $month['received_height'] = $month['received'] === 0 ? 0 : max(1, round(($month['received'] / $max) * 100));
                $month['previous_height'] = $month['previous_received'] === null || $month['previous_received'] === 0
                    ? 0
                    : max(1, round(($month['previous_received'] / $max) * 100));

                return $month;
            }, $months),
            'max' => $max,
        ];
    }

    private function dailyTotals(array $filters, CarbonImmutable $from, CarbonImmutable $to): array
    {
        $dailyTotals = $this->registrations($filters, $from, $to)
            ->select('registrations.date')
            ->selectRaw('SUM(registrations.num_samples_received) as received')
            ->groupBy('registrations.date')
            ->orderBy('registrations.date')
            ->get()
            ->mapWithKeys(fn ($row) => [CarbonImmutable::parse($row->date)->format('Y-m-d') => (int) $row->received])
            ->all();

        return $this->sumByMonth($dailyTotals);
    }

    private function sumByMonth(array $dailyTotals): array
    {
        $monthly = [];

        foreach ($dailyTotals as $date => $received) {
            $month = substr($date, 0, 7);
            $monthly[$month] = ($monthly[$month] ?? 0) + $received;
        }

        return $monthly;
    }

    private function registrations(array $filters, ?CarbonImmutable $from = null, ?CarbonImmutable $to = null): Builder
    {
        $query = Registration::query()
            ->where('registrations.status', RecordStatusEnum::Active)
            ->countsTowardLabTotals();

        if ($from && $to) {
            $query->whereDate('registrations.date', '>=', $from->toDateString())
                ->whereDate('registrations.date', '<=', $to->toDateString());
        }

        if (filled($filters['customer_id'] ?? null)) {
            $query->where('registrations.customer_id', (int) $filters['customer_id']);
        }

        if (filled($filters['customer_category_id'] ?? null)) {
            $query->whereHas('customer', fn (Builder $customer) => $customer->where('customer_category_id', (int) $filters['customer_category_id']));
        }

        if (filled($filters['sample_category_id'] ?? null)) {
            $query->where('registrations.lab_sample_category_id', (int) $filters['sample_category_id']);
        }

        if (($filters['contract_scope'] ?? 'all') === 'with') {
            $query->where(function (Builder $query) {
                $query->whereNotNull('registrations.contract_id')
                    ->orWhereExists(function ($linkedContractSample) {
                        $linkedContractSample->selectRaw('1')
                            ->from('contract_samples as linked_contract_sample')
                            ->whereColumn('linked_contract_sample.id', 'registrations.contract_sample_id')
                            ->whereNull('linked_contract_sample.deleted_at');
                    });
            });
        } elseif (($filters['contract_scope'] ?? 'all') === 'without') {
            $query->whereNull('registrations.contract_id')
                ->whereNotExists(function ($linkedContractSample) {
                    $linkedContractSample->selectRaw('1')
                        ->from('contract_samples as linked_contract_sample')
                        ->whereColumn('linked_contract_sample.id', 'registrations.contract_sample_id')
                        ->whereNull('linked_contract_sample.deleted_at');
                });
        }

        return $query;
    }

    private function compareMetricSets(array $current, ?array $previous): array
    {
        return collect($current)->map(function ($value, $metric) use ($previous) {
            $prior = $previous[$metric] ?? null;

            return [
                'current' => $value,
                'previous' => $prior,
                'change' => $prior === null ? null : $value - $prior,
                'change_percent' => $metric === 'invalid_rate' || $prior === null || $prior === 0
                    ? null
                    : round((($value - $prior) / $prior) * 100, 1),
            ];
        })->all();
    }
}
