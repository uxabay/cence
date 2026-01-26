<?php

namespace App\Models;

use App\Enums\RecordStatusEnum;
use App\Enums\CostCalculationTypeEnum;
use App\Models\LabAnalysis;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int|null $analyses_count
 * @property float|null $analysis_unit_price_snapshot
 * @property float|null $calculated_unit_price
 * @property float|null $calculated_total
 */
class Registration extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $table = 'registrations';

    protected $fillable = [
        'date',
        'registration_number',
        'lab_sample_category_id',
        'contract_id',
        'contract_sample_id',
        'customer_id',
        'num_samples_received',
        'not_valid_samples',
        'total_samples',
        'unit_price_snapshot',
        'analysis_unit_price_snapshot', // ← v1.1.0
        'analyses_count',               // ← v1.1.0
        'currency_code',
        'year',
        'comments',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'date' => 'date',
        'year' => 'integer',
        'num_samples_received' => 'integer',
        'not_valid_samples' => 'integer',
        'total_samples' => 'integer',
        'unit_price_snapshot' => 'decimal:2',
        'analysis_unit_price_snapshot' => 'decimal:2',  // ← v1.1.0
        'analyses_count' => 'integer',                  // ← v1.1.0
        'calculated_unit_price' => 'decimal:2',   // ← ΝΕΟ
        'calculated_total' => 'decimal:2',        // ← ΝΕΟ
        'status' => RecordStatusEnum::class,
    ];

    /*
    |--------------------------------------------------------------------------
    | Boot events for audit fields
    |--------------------------------------------------------------------------
    */
    protected static function booted(): void
    {
        static::creating(function ($model) {
            if (Auth::check()) {
                $model->created_by = Auth::id();
                $model->updated_by = Auth::id();
            }

            // valid samples calculation
            $model->total_samples = max(
                0,
                ($model->num_samples_received ?? 0) - ($model->not_valid_samples ?? 0)
            );
            $model->calculateCost();
        });

        static::updating(function ($model) {
            if (Auth::check()) {
                $model->updated_by = Auth::id();
            }

            // valid samples calculation
            $model->total_samples = max(
                0,
                ($model->num_samples_received ?? 0) - ($model->not_valid_samples ?? 0)
            );
            $model->calculateCost();
        });

        static::saved(function ($registration) {
            app(\App\Services\Contracts\ContractNotificationService::class)
                ->evaluateRegistration($registration);
        });

    }

    /*
    |--------------------------------------------------------------------------
    | Activity Log
    |--------------------------------------------------------------------------
    */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('registration')
            ->logFillable()
            ->setDescriptionForEvent(fn (string $eventName) => "Registration record has been {$eventName}");
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */
    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class, 'contract_id');
    }

    public function contractSample(): BelongsTo
    {
        return $this->belongsTo(ContractSample::class, 'contract_sample_id');
    }

    public function labCategory(): BelongsTo
    {
        return $this->belongsTo(LabSampleCategory::class, 'lab_sample_category_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(LabCustomer::class, 'customer_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function analyses(): HasMany
    {
        return $this->hasMany(RegistrationAnalysis::class, 'registration_id');
    }


    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */
    public function scopeActive($query)
    {
        return $query->where('status', RecordStatusEnum::Active);
    }

    public function scopeInactive($query)
    {
        return $query->where('status', RecordStatusEnum::Inactive);
    }

    public function scopeBetweenDates($query, ?string $from = null, ?string $to = null)
    {
        if ($from) {
            $query->whereDate('date', '>=', $from);
        }

        if ($to) {
            $query->whereDate('date', '<=', $to);
        }

        return $query;
    }

    public function scopeForYear($query, int $year)
    {
        return $query->whereYear('date', $year);
    }

    public function scopeByContract($query, int $contractId)
    {
        return $query->where('contract_id', $contractId);
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors & Helpers
    |--------------------------------------------------------------------------
    */
    public function getStatusLabelAttribute(): string
    {
        return $this->status?->getLabel() ?? '-';
    }

    public function getDisplayLabelAttribute(): string
    {
        $num = $this->registration_number ?? '—';
        $date = $this->date?->format('d/m/Y') ?? '-';
        return "Πρωτόκολλο {$num} ({$date})";
    }

    public function getSampleSummaryAttribute(): string
    {
        return "{$this->total_samples} δείγματα (από {$this->num_samples_received}, μη έγκυρα {$this->not_valid_samples})";
    }

    public function getAmountSnapshotAttribute(): string
    {
        if ($this->unit_price_snapshot) {
            $amount = $this->total_samples * $this->unit_price_snapshot;
            return number_format($amount, 2) . ' €';
        }

        return '-';
    }

    public function calculateCost(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Guard: χωρίς ContractSample δεν υπολογίζουμε κόστος
        |--------------------------------------------------------------------------
        */
        if (! $this->contractSample) {
            $this->calculated_unit_price = null;
            $this->calculated_total = null;
            return;
        }

        $sample = $this->contractSample;

        // Βασικά δεδομένα σύμβασης
        $fixPrice = (float) $sample->price;                 // fix τιμή δείγματος
        $maxAnalyses = (int) $sample->max_analyses;         // όριο αναλύσεων
        $type = $sample->cost_calculation_type;             // τρόπος υπολογισμού

        /*
        |--------------------------------------------------------------------------
        | FIX
        |--------------------------------------------------------------------------
        | Πάντα fix τιμή ανά δείγμα
        */
        if ($type === \App\Enums\CostCalculationTypeEnum::FIX) {
            $this->calculated_unit_price = $fixPrice;
            $this->calculated_total = $fixPrice * $this->total_samples;
            return;
        }

        /*
        |--------------------------------------------------------------------------
        | VARIABLE_COUNT (NEW)
        |--------------------------------------------------------------------------
        | Ο χρήστης δηλώνει συνολικό αριθμό αναλύσεων.
        | Κάθε ανάλυση τιμολογείται με fix τιμή ανάλυσης (snapshot).
        | Αν ξεπεραστεί το όριο → fallback σε FIX.
        */
        if ($type === \App\Enums\CostCalculationTypeEnum::VARIABLE_COUNT) {

            $analysesCount = (int) ($this->analyses_count ?? 0);
            $analysisUnitPrice = (float) ($this->analysis_unit_price_snapshot ?? 0);

            // Χωρίς αναλύσεις ή υπέρβαση ορίου → FIX
            if (
                $analysesCount === 0 ||
                ($maxAnalyses > 0 && $analysesCount > $maxAnalyses)
            ) {
                $this->calculated_unit_price = $fixPrice;
                $this->calculated_total = $fixPrice * $this->total_samples;
                return;
            }

            // Υπολογισμός: αριθμός αναλύσεων × τιμή ανάλυσης
            $unitPrice = $analysesCount * $analysisUnitPrice;

            $this->calculated_unit_price = $unitPrice;
            $this->calculated_total = $unitPrice * $this->total_samples;
            return;
        }

        /*
        |--------------------------------------------------------------------------
        | VARIABLE (LEGACY)
        |--------------------------------------------------------------------------
        | Αναλύσεις καταχωρούνται μία-μία (RegistrationAnalysis).
        | Υπολογισμός από άθροισμα analysis_price.
        | Αν ξεπεραστεί το όριο → fallback σε FIX.
        */
        $analyses = $this->analyses;
        $analysesCount = $analyses->count();
        $sumAnalyses = $analyses->sum('analysis_price');

        // αποθηκεύουμε snapshot αριθμού αναλύσεων
        $this->analyses_count = $analysesCount;

        // Χωρίς αναλύσεις ή υπέρβαση ορίου → FIX
        if (
            $analysesCount === 0 ||
            ($maxAnalyses > 0 && $analysesCount > $maxAnalyses)
        ) {
            $this->calculated_unit_price = $fixPrice;
            $this->calculated_total = $fixPrice * $this->total_samples;
            return;
        }

        // Υπολογισμός από άθροισμα τιμών αναλύσεων
        $this->calculated_unit_price = $sumAnalyses;
        $this->calculated_total = $sumAnalyses * $this->total_samples;
    }

    public function getCustomerContractInfoAttribute(): string
    {
        $customerId = $this->customer_id;

        if (!$customerId) {
            return '<em>Δεν έχει επιλεγεί πελάτης.</em>';
        }

        $contract = \App\Models\Contract::where('lab_customer_id', $customerId)
            ->where('status', \App\Enums\RecordStatusEnum::Active)
            ->orderByDesc('date_start')
            ->first();

        if (!$contract) {
            return '<span class="text-red-700">Ο πελάτης δεν έχει ενεργή σύμβαση.</span>';
        }

        $url = route('filament.admin.resources.contracts.view', $contract->id);

        return sprintf(
            '<span class="text-green-800 font-medium">Ενεργή σύμβαση: </span>
            <a href="%s" target="_blank" class="text-primary-600 underline hover:text-primary-800">%s – %s</a><br>
            <em>Διάρκεια: </em> %s έως %s',
            e($url),
            e($contract->contract_number ?? '—'),
            e($contract->title ?? ''),
            e($contract->date_start?->format('d/m/Y') ?? '-'),
            e($contract->date_end?->format('d/m/Y') ?? '-')
        );
    }

    public static function nextNumberForYear(int $year): string
    {
        $last = static::where('year', $year)
            ->latest('id')
            ->value('registration_number');

        if (! $last || ! preg_match('/^(\d{1,})\//', $last, $m)) {
            return sprintf('%05d/%s', 1, $year);
        }

        return sprintf('%05d/%s', ((int) $m[1]) + 1, $year);
    }

    /**
     * Business rules
     */
    public function canBeDeleted(): bool
    {
        return true;
    }

}
