<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class ContractYear extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $table = 'contract_years';

    protected $fillable = [
        'contract_id',
        'year',
        'annual_budget',
        'samples_planned',
        'samples_actual',
        'amount_used',
        'amount_remaining',
        'remarks',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'year' => 'integer',
        'annual_budget' => 'decimal:2',
        'amount_used' => 'decimal:2',
        'amount_remaining' => 'decimal:2',
    ];

    /*
    |--------------------------------------------------------------------------
    | Boot
    |--------------------------------------------------------------------------
    */
    protected static function boot()
    {
        parent::boot();

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
            ->useLogName('contract.year')
            ->logFillable()
            ->setDescriptionForEvent(fn (string $eventName) => "Contract year has been {$eventName}");
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */
    public function registrations()
    {
        return $this->hasMany(Registration::class, 'contract_year_id');
    }

    public function contract()
    {
        return $this->belongsTo(Contract::class);
    }

    public function yearSamples()
    {
        return $this->hasMany(ContractYearSample::class);
    }

    public function reallocations()
    {
        return $this->hasMany(ContractSampleReallocation::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */
    public function getExecutionRateAttribute(): float
    {
        return $this->samples_planned > 0
            ? round(($this->samples_actual / $this->samples_planned) * 100, 2)
            : 0;
    }

    public function getBudgetUsageRateAttribute(): float
    {
        return $this->annual_budget > 0
            ? round(($this->amount_used / $this->annual_budget) * 100, 2)
            : 0;
    }

    public function getValidSamplesCountAttribute(): int
    {
        return $this->registrations()->sum('valid_samples');
    }

    public function getYearlyExecutionPercentageAttribute(): float
    {
        $expected = $this->contractSampleTypes()->sum('planned_samples');
        if ($expected <= 0) return 0;

        return round(($this->valid_samples_count / $expected) * 100, 2);
    }

}
