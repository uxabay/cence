<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class ContractSampleReallocation extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $table = 'contract_sample_reallocations';

    protected $fillable = [
        'contract_id',
        'contract_year_id',
        'from_sample_type_id',
        'to_sample_type_id',
        'samples_moved',
        'amount_moved',
        'rationale',
        'contract_revision_id',
        'approved_by',
        'approved_at',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'amount_moved' => 'decimal:2',
        'approved_at' => 'datetime',
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
            ->useLogName('contract.reallocation')
            ->logFillable()
            ->setDescriptionForEvent(fn (string $eventName) => "Contract sample reallocation has been {$eventName}");
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */
    public function contract()
    {
        return $this->belongsTo(Contract::class);
    }

    public function contractYear()
    {
        return $this->belongsTo(ContractYear::class);
    }

    public function fromSampleType()
    {
        return $this->belongsTo(ContractSampleType::class, 'from_sample_type_id');
    }

    public function toSampleType()
    {
        return $this->belongsTo(ContractSampleType::class, 'to_sample_type_id');
    }

    public function contractRevision()
    {
        return $this->belongsTo(ContractRevision::class, 'contract_revision_id');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
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
    public function getDirectionLabelAttribute(): string
    {
        return "{$this->fromSampleType->name} → {$this->toSampleType->name}";
    }

    public function getAmountFormattedAttribute(): string
    {
        return number_format($this->amount_moved, 2, ',', '.') . ' €';
    }

    public function getSamplesFormattedAttribute(): string
    {
        return $this->samples_moved . ' δείγματα';
    }
}
