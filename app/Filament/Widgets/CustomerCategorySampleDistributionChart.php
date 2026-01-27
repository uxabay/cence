<?php

namespace App\Filament\Widgets;

use App\Models\CustomerCategory;
use App\Models\Registration;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;

class CustomerCategorySampleDistributionChart extends ChartWidget
{
    use HasWidgetShield;
    
    protected ?string $heading = 'Δείγματα ανά Κατηγορία Πελάτη (Τελευταίες 5 Εβδομάδες)';
    protected static ?int $sort = 30;
    protected int|string|array $columnSpan = 2;

    protected function getType(): string
    {
        return 'pie';
    }

    protected function getData(): array
    {
        // ---------------------------------------------------------
        // 1. Rolling 5 ISO weeks (όπως στο προηγούμενο γράφημα)
        // ---------------------------------------------------------
        $now = Carbon::now();
        $startDate = $now->copy()->startOfWeek(Carbon::MONDAY)->subWeeks(4);

        // ---------------------------------------------------------
        // 2. Φέρνουμε registrations με πελάτη & κατηγορία
        // ---------------------------------------------------------
        $regs = Registration::active()
            ->whereDate('date', '>=', $startDate->toDateString())
            ->whereDate('date', '<=', $now->toDateString())
            ->whereNotNull('customer_id')
            ->with(['customer.category:id,name'])
            ->select(['id', 'date', 'customer_id', 'total_samples'])
            ->get();

        // ---------------------------------------------------------
        // 3. Συγκέντρωση δειγμάτων ανά κατηγορία πελάτη
        // ---------------------------------------------------------
        $categoryTotals = [];

        foreach ($regs as $reg) {
            $categoryName = $reg->customer->category->name ?? 'Άγνωστη κατηγορία';

            if (!isset($categoryTotals[$categoryName])) {
                $categoryTotals[$categoryName] = 0;
            }

            $categoryTotals[$categoryName] += $reg->total_samples ?? 0;
        }

        // Αν δεν υπάρχουν δεδομένα → minimum setup
        if (empty($categoryTotals)) {
            return [
                'labels' => [],
                'datasets' => [
                    [
                        'label' => 'Δείγματα',
                        'data' => [],
                    ],
                ],
            ];
        }

        // ---------------------------------------------------------
        // 4. Labels & Data
        // ---------------------------------------------------------
        $labels = array_keys($categoryTotals);
        $values = array_values($categoryTotals);

        // ---------------------------------------------------------
        // 5. Χρώματα (auto palette)
        // ---------------------------------------------------------
        $colors = [
            '#60a5fa', // blue
            '#34d399', // green
            '#fbbf24', // amber
            '#f87171', // red
            '#a78bfa', // violet
            '#2dd4bf', // teal
            '#fb7185', // rose
            '#facc15', // yellow
        ];

        // Repeat colors if categories > colors
        while (count($colors) < count($labels)) {
            $colors = array_merge($colors, $colors);
        }

        // ---------------------------------------------------------
        // 6. Chart output
        // ---------------------------------------------------------
        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Δείγματα',
                    'data' => $values,
                    'backgroundColor' => array_slice($colors, 0, count($labels)),
                ],
            ],
        ];
    }
}
