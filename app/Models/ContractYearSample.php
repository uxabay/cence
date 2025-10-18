<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class ContractYearSample extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $table = 'contract_year_samples';

    protected $fillable = [
        'contract_year_id',
        'contract_sample_type_id',
        'planned_samples',
        'executed_samples',
        'unit_cost',
        'total_cost',
        'remarks',
    ];

    protected $casts = [
        'unit_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
    ];

    /*
    |--------------------------------------------------------------------------
    | Activity Log
    |--------------------------------------------------------------------------
    */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('contract.year_sample')
            ->logFillable()
            ->setDescriptionForEvent(fn (string $eventName) => "Contract year sample has been {$eventName}");
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */
    public function contractYear()
    {
        return $this->belongsTo(ContractYear::class);
    }

    public function contractSampleType()
    {
        return $this->belongsTo(ContractSampleType::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */
    public function getTotalCostCalculatedAttribute(): float
    {
        return $this->planned_samples * $this->unit_cost;
    }

    public function getExecutionRateAttribute(): float
    {
        return $this->planned_samples > 0
            ? round(($this->executed_samples / $this->planned_samples) * 100, 2)
            : 0;
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */
    public function syncTotalCost(): void
    {
        $this->total_cost = $this->getTotalCostCalculatedAttribute();
        $this->save();
    }
}
