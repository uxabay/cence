<?php

namespace App\Models;

use App\Enums\RegistrationStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Illuminate\Support\Facades\Auth;

class Registration extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $table = 'registrations';

    protected $fillable = [
        'contract_id',
        'contract_year_id',
        'contract_sample_type_id',
        'lab_customer_id',
        'lab_sample_category_id',
        'protocol_number',
        'protocol_date',
        'total_samples',
        'invalid_samples',
        'valid_samples',
        'remarks',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'protocol_date' => 'date',
        'status' => RegistrationStatusEnum::class,
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (Auth::check()) {
                $model->created_by = Auth::id();
                $model->updated_by = Auth::id();
            }

            if (!isset($model->valid_samples)) {
                $model->valid_samples = max(0, $model->total_samples - $model->invalid_samples);
            }
        });

        static::updating(function ($model) {
            if (Auth::check()) {
                $model->updated_by = Auth::id();
            }

            $model->valid_samples = max(0, $model->total_samples - $model->invalid_samples);
        });
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('contract.registration')
            ->logFillable()
            ->setDescriptionForEvent(fn(string $eventName) => "Registration has been {$eventName}");
    }

    /**
     * Relationships
     */
    public function contract()
    {
        return $this->belongsTo(Contract::class);
    }

    public function contractYear()
    {
        return $this->belongsTo(ContractYear::class);
    }

    public function contractSampleType()
    {
        return $this->belongsTo(ContractSampleType::class);
    }

    public function customer()
    {
        return $this->belongsTo(LabCustomer::class, 'lab_customer_id');
    }

    public function labSampleCategory()
    {
        return $this->belongsTo(LabSampleCategory::class, 'lab_sample_category_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Accessors
     */
    public function getCoveragePercentageAttribute(): float
    {
        return $this->total_samples > 0
            ? round(($this->valid_samples / $this->total_samples) * 100, 2)
            : 0;
    }

    public function getStatusLabelAttribute(): string
    {
        return $this->status?->getLabel() ?? '-';
    }

    public function getIsConfirmedAttribute(): bool
    {
        return $this->status === RegistrationStatusEnum::Confirmed;
    }
}
