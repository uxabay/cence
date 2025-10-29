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

class ContractRevision extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $table = 'contract_revisions';

    protected $fillable = [
        'contract_id',
        'from_sample_id',
        'to_sample_id',
        'num_of_samples',
        'amount_delta',
        'date',
        'year',
        'notes',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'status' => RecordStatusEnum::class,
        'date' => 'date',
        'year' => 'integer',
        'num_of_samples' => 'integer',
        'amount_delta' => 'decimal:2',
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
            ->useLogName('contract.revision')
            ->logFillable()
            ->setDescriptionForEvent(fn (string $eventName) => "Contract revision record has been {$eventName}");
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

    public function fromSample(): BelongsTo
    {
        return $this->belongsTo(ContractSample::class, 'from_sample_id');
    }

    public function toSample(): BelongsTo
    {
        return $this->belongsTo(ContractSample::class, 'to_sample_id');
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
    | Accessors & Helpers
    |--------------------------------------------------------------------------
    */
    public function getStatusLabelAttribute(): string
    {
        return $this->status?->getLabel() ?? '-';
    }

    public function getDisplayLabelAttribute(): string
    {
        $from = $this->fromSample?->name ?? '-';
        $to = $this->toSample?->name ?? '-';
        return "Από {$from} ➜ Σε {$to}";
    }

    public function getFormattedAmountDeltaAttribute(): string
    {
        if (is_null($this->amount_delta)) {
            return '-';
        }

        $formatted = number_format(abs($this->amount_delta), 2) . ' €';
        return $this->amount_delta >= 0 ? "+{$formatted}" : "-{$formatted}";
    }

    /*
    |--------------------------------------------------------------------------
    | Business Logic Helpers
    |--------------------------------------------------------------------------
    */

    /**
     * Ελέγχει αν η αναδιανομή είναι ισορροπημένη (ίδιο contract_id)
     */
    public function belongsToSameContract(): bool
    {
        return $this->fromSample?->contract_id === $this->toSample?->contract_id;
    }

    /**
     * Υπολογίζει την καθαρή μεταβολή ποσότητας (με πρόσημο)
     */
    public function getNetSampleChange(): int
    {
        return $this->num_of_samples ?? 0;
    }
}
