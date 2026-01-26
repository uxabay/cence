<?php

namespace App\Models;

use App\Enums\RecordStatusEnum;
use App\Enums\CostCalculationTypeEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ContractSample extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $table = 'contract_samples';

    protected $fillable = [
        'contract_id',
        'contract_sample_category_id',
        'name',
        'is_master',
        'year',
        'forecasted_samples',
        'price',
        'analysis_unit_price',      // ← v1.1.0
        'forecasted_amount',
        'currency_code',
        'remarks',
        'status',
        'cost_calculation_type',    // ← Νέο
        'max_analyses',             // ← Νέο
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'status' => RecordStatusEnum::class,
        'is_master' => 'boolean',
        'year' => 'integer',
        'forecasted_samples' => 'integer',
        'price' => 'decimal:2',
        'analysis_unit_price' => 'decimal:2',
        'forecasted_amount' => 'decimal:2',
        'cost_calculation_type' => CostCalculationTypeEnum::class, // ← ΝΕΟ
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
        });

        static::updating(function ($model) {
            if (Auth::check()) {
                $model->updated_by = Auth::id();
            }
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
            ->useLogName('contract.sample')
            ->logFillable()
            ->setDescriptionForEvent(fn (string $eventName) => "Contract sample record has been {$eventName}");
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

    public function category(): BelongsTo
    {
        return $this->belongsTo(ContractSampleCategory::class, 'contract_sample_category_id');
    }

    public function labCategories(): BelongsToMany
    {
        return $this->belongsToMany(LabSampleCategory::class, 'contract_sample_lab_category', 'contract_sample_id', 'lab_sample_category_id')
                    ->withTimestamps();
    }

    public function revisionsFrom(): HasMany
    {
        return $this->hasMany(ContractRevision::class, 'from_sample_id');
    }

    public function revisionsTo(): HasMany
    {
        return $this->hasMany(ContractRevision::class, 'to_sample_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
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

    public function scopeByYear($query, int $year)
    {
        return $query->where('year', $year);
    }

    /*
    |--------------------------------------------------------------------------
    | Forecasted with revisions
    |--------------------------------------------------------------------------
    */

    public function getNetForecastedSamplesAttribute(): int
    {
        $base = $this->forecasted_samples;

        $out = $this->revisionsFrom()->active()->sum('num_of_samples');
        $in  = $this->revisionsTo()->active()->sum('num_of_samples');

        return $base - $out + $in;
    }

    public function getNetForecastedAmountAttribute(): float
    {
        $base = $this->forecasted_amount;

        $outDelta = $this->revisionsFrom()->active()->sum('amount_delta');
        $inDelta  = $this->revisionsTo()->active()->sum('amount_delta');

        return round($base - $outDelta + $inDelta, 2);
    }

    /*
    |--------------------------------------------------------------------------
    | Actual calculations from Registrations
    |--------------------------------------------------------------------------
    */

    public function getActualSamples(?string $from = null, ?string $to = null): int
    {
        return \App\Models\Registration::query()
            ->where('contract_sample_id', $this->id)
            ->active()
            ->betweenDates($from, $to)
            ->sum('total_samples');
    }

    public function getActualAmount(?string $from = null, ?string $to = null): float
    {
        return \App\Models\Registration::query()
            ->where('contract_sample_id', $this->id)
            ->active()
            ->betweenDates($from, $to)
            ->sum('calculated_total');
    }


    // Accessors for Filament compatibility
    public function getActualSamplesAttribute(): int
    {
        return $this->getActualSamples();
    }

    public function getActualAmountAttribute(): float
    {
        return $this->getActualAmount();
    }

    /*
    |--------------------------------------------------------------------------
    | Misc Accessors
    |--------------------------------------------------------------------------
    */
    public function getStatusLabelAttribute(): string
    {
        return $this->status?->getLabel() ?? '-';
    }

    public function getDisplayLabelAttribute(): string
    {
        return $this->name
            ? "{$this->name} ({$this->category?->name})"
            : $this->category?->name ?? '-';
    }

    public function getCalculatedAmountAttribute(): float
    {
        return round($this->forecasted_samples * $this->price, 2);
    }

    public function getEffectiveAmount(): float
    {
        return $this->forecasted_amount > 0
            ? (float) $this->forecasted_amount
            : $this->getCalculatedAmountAttribute();
    }

    public function isMaster(): bool
    {
        return (bool) $this->is_master;
    }
}
