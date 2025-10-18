<?php

namespace App\Models;

use App\Enums\ContractStatusEnum;
use App\Enums\ContractTypeEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Contract extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $table = 'contracts';

    protected $fillable = [
        'contract_number',
        'title',
        'subject',
        'contract_type',
        'lab_customer_id',
        'start_date',
        'end_date',
        'total_value',
        'funding_source',
        'scope',
        'status',
        'parent_id',
        'remarks',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'total_value' => 'decimal:2',
        'status' => ContractStatusEnum::class,
        'contract_type' => ContractTypeEnum::class,
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
            ->useLogName('contract')
            ->logFillable()
            ->setDescriptionForEvent(fn(string $eventName) => "Contract has been {$eventName}");
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */
    public function registrations()
    {
        return $this->hasMany(Registration::class, 'contract_id');
    }

    public function labCustomer()
    {
        return $this->belongsTo(LabCustomer::class, 'lab_customer_id');
    }

    public function parentContract()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function contractYears()
    {
        return $this->hasMany(ContractYear::class);
    }

    public function sampleTypes()
    {
        return $this->hasMany(ContractSampleType::class);
    }

    public function revisions()
    {
        return $this->hasMany(ContractRevision::class);
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
    | Scopes
    |--------------------------------------------------------------------------
    */
    public function scopeActive($query)
    {
        return $query->where('status', ContractStatusEnum::Active);
    }

    public function scopeExpired($query)
    {
        return $query->where('status', ContractStatusEnum::Expired);
    }

     public function scopeActiveWithProgress($query)
    {
        return $query->where('status', 'active')
            ->withCount(['registrations as valid_samples_sum' => fn($q) => $q->select(DB::raw('sum(valid_samples)'))]);
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */
    public function getDurationAttribute(): string
    {
        if (!$this->start_date || !$this->end_date) {
            return '-';
        }

        return $this->start_date->format('d/m/Y') . ' - ' . $this->end_date->format('d/m/Y');
    }

    public function getTotalValueFormattedAttribute(): string
    {
        return number_format($this->total_value, 2, ',', '.') . ' €';
    }

    public function getTotalValidSamplesAttribute(): int
    {
        return $this->registrations()->sum('valid_samples');
    }

    public function getExecutionPercentageAttribute(): float
    {
        $expected = $this->contractSampleTypes()->sum('expected_samples'); // αν υπάρχει τέτοιο πεδίο
        if ($expected <= 0) return 0;

        return round(($this->total_valid_samples / $expected) * 100, 2);
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */
    public function getDisplayNameAttribute(): string
    {
        // Αριθμός σύμβασης
        $number = $this->contract_number ? "Σύμβαση {$this->contract_number}" : "Σύμβαση";

        // Πελάτης ή τίτλος
        $descriptor = $this->title
            ? $this->title
            : ($this->customer?->name ?? '');

        // Έτος (αν υπάρχει ημερομηνία έναρξης)
        $year = $this->start_date?->format('Y');

        // Συνδυασμός περιεκτικός και καθαρός
        $parts = array_filter([$number, $descriptor, $year]);

        return implode(' – ', $parts);
    }

}
