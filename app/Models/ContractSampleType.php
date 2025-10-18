<?php

namespace App\Models;

use App\Enums\ContractSampleStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class ContractSampleType extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $table = 'contract_sample_types';

    protected $fillable = [
        'contract_id',
        'name',
        'description',
        'unit_cost',
        'planned_samples',
        'total_cost',
        'status',
        'flags',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'unit_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'flags' => 'array',
        'status' => ContractSampleStatusEnum::class,
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
            ->useLogName('contract.sample_type')
            ->logFillable()
            ->setDescriptionForEvent(fn (string $eventName) => "Contract sample type has been {$eventName}");
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */
    public function registrations()
    {
        return $this->hasMany(Registration::class, 'contract_sample_type_id');
    }

    public function contract()
    {
        return $this->belongsTo(Contract::class);
    }

    public function labSampleCategories()
    {
        return $this->belongsToMany(
            LabSampleCategory::class,
            'contract_sample_type_lab_sample_category'
        )->withPivot([
            'include_virtual',
            'include_lab_count',
            'include_contract_count',
            'weight',
            'notes',
        ])->withTimestamps();
    }

    public function yearSamples()
    {
        return $this->hasMany(ContractYearSample::class);
    }

    public function reallocationsFrom()
    {
        return $this->hasMany(ContractSampleReallocation::class, 'from_sample_type_id');
    }

    public function reallocationsTo()
    {
        return $this->hasMany(ContractSampleReallocation::class, 'to_sample_type_id');
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
    public function getTotalCostCalculatedAttribute(): float
    {
        return $this->planned_samples * $this->unit_cost;
    }

    public function syncTotalCost(): void
    {
        $this->total_cost = $this->getTotalCostCalculatedAttribute();
        $this->save();
    }

    public function getValidSamplesCountAttribute(): int
    {
        return $this->registrations()->sum('valid_samples');
    }

    public function getCoveragePercentageAttribute(): float
    {
        if ($this->planned_samples <= 0) return 0;

        return round(($this->valid_samples_count / $this->planned_samples) * 100, 2);
    }

}
