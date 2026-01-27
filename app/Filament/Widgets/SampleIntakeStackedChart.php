<?php

namespace App\Filament\Widgets;

use App\Models\Registration;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;

class SampleIntakeStackedChart extends ChartWidget
{
    use HasWidgetShield;
    
    protected ?string $heading = 'Εβδομαδιαία Ροή Δειγμάτων (Τελευταίες 5 Εβδομάδες)';
    protected static ?int $sort = 20;
    protected int|string|array $columnSpan = 2;

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        // ---------------------------------------------------------
        // 1. Υπολογισμός τελευταίων 5 ISO εβδομάδων
        // ---------------------------------------------------------
        $now = Carbon::now();
        $startDate = $now->copy()->startOfWeek(Carbon::MONDAY)->subWeeks(4);

        // ISO week keys: "2025-51", "2025-52", "2026-01", ...
        $weekKeys = [];
        for ($i = 4; $i >= 0; $i--) {
            $weekDate = $now->copy()->subWeeks($i);
            $weekKeys[] = $weekDate->format('o-W');
        }

        // Δημιουργούμε δομές με μηδενικά για να είναι πάντα 5 εβδομάδες
        $withContract = array_fill_keys($weekKeys, 0);
        $withoutContract = array_fill_keys($weekKeys, 0);

        // ---------------------------------------------------------
        // 2. Φέρνουμε registrations των τελευταίων 5 εβδομάδων
        // ---------------------------------------------------------
        $regs = Registration::active()
            ->whereDate('date', '>=', $startDate->toDateString())
            ->whereDate('date', '<=', $now->toDateString())
            ->select(['id', 'date', 'contract_id', 'total_samples'])
            ->get();

        // ---------------------------------------------------------
        // 3. Grouping ανά ISO week-year & μέτρηση δειγμάτων
        // ---------------------------------------------------------
        foreach ($regs as $reg) {
            $weekKey = $reg->date->format('o-W');

            if (!isset($withContract[$weekKey])) {
                // Το κάνουμε skip αν για κάποιο λόγο είναι εκτός εύρους
                continue;
            }

            if ($reg->contract_id) {
                $withContract[$weekKey] += $reg->total_samples ?? 0;
            } else {
                $withoutContract[$weekKey] += $reg->total_samples ?? 0;
            }
        }

        // ---------------------------------------------------------
        // 4. Labels σε ωραία μορφή ("Εβδ. 51/2025")
        // ---------------------------------------------------------
        $labels = array_map(function ($key) {
            [$year, $week] = explode('-', $key);
            return "Εβδ. {$week}/{$year}";
        }, $weekKeys);

        // ---------------------------------------------------------
        // 5. Stacked Chart.js config
        // ---------------------------------------------------------
        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Με σύμβαση',
                    'data' => array_values($withContract),
                    'backgroundColor' => '#4ade80', // πράσινο
                ],
                [
                    'label' => 'Χωρίς σύμβαση',
                    'data' => array_values($withoutContract),
                    'backgroundColor' => '#f87171', // κόκκινο
                ],
            ],
            'options' => [
                'responsive' => true,
                'scales' => [
                    'x' => [
                        'stacked' => true,
                    ],
                    'y' => [
                        'stacked' => true,
                        'beginAtZero' => true,
                    ],
                ],
            ],
        ];
    }
}
