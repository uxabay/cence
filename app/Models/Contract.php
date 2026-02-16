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

    /**
     * Populate audit fields when creating/updating records.
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

    /**
     * Configure Spatie activity log defaults for contracts.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('contract')
            ->logFillable()
            ->setDescriptionForEvent(fn (string $eventName) => "Contract record has been {$eventName}");
    }

    /**
     * Model relationships.
     */
    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class, 'contract_id');
    }

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

    /**
     * Query scopes.
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

    /**
     * Accessors & helper methods.
     */
    public function refreshAndCheckNotifications(): void
    {
        app(\App\Services\Contracts\ContractNotificationService::class)
            ->evaluateContract($this);
    }

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
     * Total forecasted amount across all samples.
     */
    public function getForecastedAmountAttribute(): float
    {
        return $this->samples()->sum('forecasted_amount');
    }

    /**
     * Aggregate statistics (optionally filtered by date range).
     */
    public function getStats(string|null $from = null, string|null $to = null): array
    {
        $samples = $this->samples()->with('category')->get();

        // Group samples by category for per-category rollups.
        $grouped = $samples->groupBy('category.id');

        $forecastedSamples = 0;
        $forecastedAmount = 0;
        $actualSamples = 0;
        $actualAmount = 0;

        foreach ($grouped as $categorySamples) {

            // Forecasted totals.
            $forecastedSamples += $categorySamples->sum('net_forecasted_samples');
            $forecastedAmount  += $categorySamples->sum('net_forecasted_amount');

            // Actual samples (preserves existing calculation logic).
            $actualSamples += $categorySamples->sum(fn ($s) => $s->getActualSamples($from, $to));

            // Actual amounts from registrations matching the date range.
            $actualAmount += \App\Models\Registration::query()
                ->whereIn('contract_sample_id', $categorySamples->pluck('id'))
                ->active()
                ->betweenDates($from, $to)
                ->sum('calculated_total');
        }

        return [
            'forecasted_samples' => $forecastedSamples,
            'forecasted_amount'  => $forecastedAmount,
            'actual_samples'     => $actualSamples,
            'actual_amount'      => $actualAmount,
        ];
    }

    /**
     * Compatibility accessor for Filament.
     */
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
     * Flag a warning when actual samples reach 90% of forecasted samples.
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
