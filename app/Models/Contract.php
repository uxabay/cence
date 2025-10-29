<?php

namespace App\Models;

use App\Enums\RecordStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Contract extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $table = 'contracts';

    protected $fillable = [
        'title',
        'lab_customer_id',
        'contract_number',
        'date_start',
        'date_end',
        'description',
        'status',
        'file_attachment_id',
        'remarks',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'status' => RecordStatusEnum::class,
        'date_start' => 'date',
        'date_end' => 'date',
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
            ->useLogName('contract')
            ->logFillable()
            ->setDescriptionForEvent(fn (string $eventName) => "Contract record has been {$eventName}");
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(LabCustomer::class, 'lab_customer_id');
    }

    public function samples(): HasMany
    {
        return $this->hasMany(ContractSample::class, 'contract_id');
    }

    public function revisions(): HasMany
    {
        return $this->hasMany(ContractRevision::class, 'contract_id');
    }

    public function fileAttachment(): BelongsTo
    {
        return $this->belongsTo(FileAttachment::class, 'file_attachment_id');
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

    public function scopeBetweenDates($query, ?string $from = null, ?string $to = null)
    {
        if ($from) {
            $query->whereDate('date_start', '>=', $from);
        }

        if ($to) {
            $query->whereDate('date_end', '<=', $to);
        }

        return $query;
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

    public function getDisplayTitleAttribute(): string
    {
        return $this->contract_number
            ? "{$this->contract_number} – {$this->title}"
            : $this->title;
    }

    /**
     * Συνολικά προϋπολογισμένα ποσά
     */
    public function getForecastedAmountAttribute(): float
    {
        return $this->samples()->sum('forecasted_amount');
    }

    /**
     * Συγκεντρωτικά στατιστικά (με προαιρετικά φίλτρα ημερομηνιών)
     */
    public function getStats(string|null $from = null, string|null $to = null): array
    {
        $samples = $this->samples()->with('category')->get();

        // Ομαδοποίηση ανά κατηγορία
        $grouped = $samples->groupBy('category.id');

        $forecastedSamples = 0;
        $forecastedAmount = 0;
        $actualSamples = 0;
        $actualAmount = 0;

        foreach ($grouped as $categorySamples) {
            $forecastedSamples += $categorySamples->sum('net_forecasted_samples');
            $forecastedAmount  += $categorySamples->sum('net_forecasted_amount');

            $actualSamples += $categorySamples->sum(fn ($s) => $s->getActualSamples($from, $to));
            $actualAmount  += $categorySamples->sum(fn ($s) => $s->getActualAmount($from, $to));
        }

        return [
            'forecasted_samples' => $forecastedSamples,
            'forecasted_amount'  => $forecastedAmount,
            'actual_samples'     => $actualSamples,
            'actual_amount'      => $actualAmount,
        ];
    }


    // Compatibility for Filament
    public function getStatsAttribute(): array
    {
        return $this->getStats();
    }

    public function getProgressPercentage(?string $from = null, ?string $to = null): float
    {
        $stats = $this->getStats($from, $to);
        return $stats['forecasted_amount'] > 0
            ? round(($stats['actual_amount'] / $stats['forecasted_amount']) * 100, 1)
            : 0.0;
    }

    public function getProgressPercentageAttribute(): float
    {
        return $this->getProgressPercentage();
    }

    /**
     * Έλεγχος για warnings (>90%)
     */
    public function getHasWarningAttribute(): bool
    {
        $stats = $this->getStats();
        if ($stats['forecasted_samples'] === 0) {
            return false;
        }

        $ratio = $stats['actual_samples'] / $stats['forecasted_samples'];
        return $ratio >= 0.9;
    }
}
