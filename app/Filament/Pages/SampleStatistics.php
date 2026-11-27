<?php

namespace App\Filament\Pages;

use App\Models\CustomerCategory;
use App\Models\LabCustomer;
use App\Models\LabSampleCategory;
use App\Support\Statistics\SampleStatisticsService;
use BackedEnum;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class SampleStatistics extends Page
{
    protected string $view = 'filament.pages.sample-statistics';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar-square';

    protected static ?string $navigationLabel = 'Στατιστικά Δειγμάτων';

    protected static string|UnitEnum|null $navigationGroup = 'Αναφορές';

    protected static ?int $navigationSort = 90;

    protected static ?string $title = 'Στατιστικά δειγμάτων εργαστηρίου';

    public ?string $from = null;

    public ?string $to = null;

    public ?string $customerId = null;

    public ?string $customerCategoryId = null;

    public ?string $sampleCategoryId = null;

    public string $contractScope = 'all';

    public bool $comparePreviousYear = true;

    public static function canAccess(): bool
    {
        return Auth::user()?->can('view_sample_statistics') ?? false;
    }

    public function mount(): void
    {
        $this->from = now()->startOfYear()->toDateString();
        $this->to = now()->toDateString();
    }

    public function updatedFrom(): void
    {
        if (! $this->isValidDate($this->from)) {
            $this->from = now()->startOfYear()->toDateString();
        }

        if ($this->from > $this->to) {
            $this->to = $this->from;
        }
    }

    public function updatedTo(): void
    {
        if (! $this->isValidDate($this->to)) {
            $this->to = now()->toDateString();
        }

        if ($this->to < $this->from) {
            $this->from = $this->to;
        }
    }

    public function setDatePreset(string $preset): void
    {
        $today = now();

        [$from, $to] = match ($preset) {
            'current_month' => [$today->copy()->startOfMonth(), $today],
            'previous_month' => [$today->copy()->subMonthNoOverflow()->startOfMonth(), $today->copy()->subMonthNoOverflow()->endOfMonth()],
            'previous_year' => [$today->copy()->subYear()->startOfYear(), $today->copy()->subYear()->endOfYear()],
            'last_12_months' => [$today->copy()->subMonthsNoOverflow(11)->startOfMonth(), $today],
            default => [$today->copy()->startOfYear(), $today],
        };

        $this->from = $from->toDateString();
        $this->to = $to->toDateString();
    }

    public function clearFilters(): void
    {
        $this->setDatePreset('year_to_date');
        $this->customerId = null;
        $this->customerCategoryId = null;
        $this->sampleCategoryId = null;
        $this->contractScope = 'all';
        $this->comparePreviousYear = true;
    }

    protected function getViewData(): array
    {
        return [
            'statistics' => app(SampleStatisticsService::class)->build([
                'from' => $this->from ?? now()->startOfYear()->toDateString(),
                'to' => $this->to ?? now()->toDateString(),
                'customer_id' => $this->customerId,
                'customer_category_id' => $this->customerCategoryId,
                'sample_category_id' => $this->sampleCategoryId,
                'contract_scope' => in_array($this->contractScope, ['all', 'with', 'without'], true) ? $this->contractScope : 'all',
                'compare' => $this->comparePreviousYear,
            ]),
            'customers' => LabCustomer::query()->orderBy('name')->get(['id', 'name']),
            'customerCategories' => CustomerCategory::query()->orderBy('name')->get(['id', 'name']),
            'sampleCategories' => LabSampleCategory::query()->orderBy('name')->get(['id', 'name']),
        ];
    }

    private function isValidDate(?string $date): bool
    {
        if (! $date) {
            return false;
        }

        $parsed = \DateTimeImmutable::createFromFormat('!Y-m-d', $date);

        return $parsed !== false && $parsed->format('Y-m-d') === $date;
    }
}
