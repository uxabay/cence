<?php

namespace App\Models;

use App\Enums\RecordStatusEnum;
use App\Models\LabAnalysis;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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

            // Υπολογισμός valid samples
            $model->total_samples = max(
                0,
                ($model->num_samples_received ?? 0) - ($model->not_valid_samples ?? 0)
            );

            // ΝΕΟ: Υπολογισμός κόστους
            $model->calculateCost();
        });

        static::updating(function ($model) {
            if (Auth::check()) {
                $model->updated_by = Auth::id();
            }

            // Υπολογισμός valid samples
            $model->total_samples = max(
                0,
                ($model->num_samples_received ?? 0) - ($model->not_valid_samples ?? 0)
            );

            // ΝΕΟ: Υπολογισμός κόστους
            $model->calculateCost();
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

    public function analyses(): BelongsToMany
    {
        return $this->belongsToMany(LabAnalysis::class, 'registration_analysis')
            ->withPivot(['analysis_name', 'analysis_price'])
            ->withTimestamps();
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
        // Δεν υπάρχει contract sample → δεν μπορούμε να υπολογίσουμε κόστος
        if (!$this->contractSample) {
            $this->calculated_unit_price = null;
            $this->calculated_total = null;
            return;
        }

        $sample = $this->contractSample;

        // Βασικές πληροφορίες contract sample
        $price = (float) $sample->price;                // fix price
        $max = (int) $sample->max_analyses;             // threshold
        $isVariable = $sample->cost_calculation_type->value === 'variable';

        // Pivot αναλύσεων που έχουν συνδεθεί
        $analyses = $this->analyses;
        $analysesCount = $analyses->count();
        $sumAnalyses = $analyses->sum('pivot.analysis_price');

        // Αποθηκεύουμε τον αριθμό αναλύσεων στο πεδίο της βάσης
        $this->analyses_count = $analysesCount;

        /*
        |--------------------------------------------------------------------------
        | FIX PRICING
        |--------------------------------------------------------------------------
        | Αν η σύμβαση είναι FIX → Χρησιμοποιούμε πάντα το price.
        */
        if (!$isVariable) {
            $this->calculated_unit_price = $price;
            $this->calculated_total = $price * $this->total_samples;
            return;
        }

        /*
        |--------------------------------------------------------------------------
        | VARIABLE PRICING (λόγοι)
        | - η κατηγορία είναι variable
        | - πρέπει να δούμε αριθμό αναλύσεων
        |--------------------------------------------------------------------------
        */

        // Κανένας υπολογισμός αν δεν υπάρχουν αναλύσεις → FIX
        if ($analysesCount === 0) {
            $this->calculated_unit_price = $price;
            $this->calculated_total = $price * $this->total_samples;
            return;
        }

        // Αν υπάρχει όριο και ξεπέρασε → FIX
        if ($max > 0 && $analysesCount > $max) {
            $this->calculated_unit_price = $price;
            $this->calculated_total = $price * $this->total_samples;
            return;
        }

        /*
        |--------------------------------------------------------------------------
        | ΕΦΑΡΜΟΓΗ VARIABLE PRICING
        |--------------------------------------------------------------------------
        | - άθροισμα των analysis_price από το pivot
        */
        $this->calculated_unit_price = $sumAnalyses;
        $this->calculated_total = $sumAnalyses * $this->total_samples;
    }

}
