<?php

namespace App\Models;

use App\Enums\RecordStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LabSampleCategory extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $table = 'lab_sample_categories';

    protected $fillable = [
        'name',
        'short_name',
        'code',
        'description',
        'lab_id',
        'sample_type_id',
        'price',
        'method_ref',
        'standard_ref',
        'status',
        'sort_order',
        'is_counted_in_lab',
        'is_counted_in_contract',
        'is_virtual',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'status' => RecordStatusEnum::class,
        'is_counted_in_lab' => 'boolean',
        'is_counted_in_contract' => 'boolean',
        'is_virtual' => 'boolean',
    ];

    public static function boot()
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

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('lab.sample_category')
            ->logFillable()
            ->setDescriptionForEvent(fn(string $eventName) => "Lab sample category has been {$eventName}");
    }

    /**
     * Σχέσεις
     */
    public function registrations()
    {
        return $this->hasMany(Registration::class, 'lab_sample_category_id');
    }

    public function lab(): BelongsTo
    {
        return $this->belongsTo(Lab::class, 'lab_id');
    }

    public function sampleType(): BelongsTo
    {
        return $this->belongsTo(SampleType::class, 'sample_type_id');
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
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('status', RecordStatusEnum::Active);
    }

    public function scopeInactive($query)
    {
        return $query->where('status', RecordStatusEnum::Inactive);
    }

    /**
     * Accessors & Helpers
     */
    public function getFullNameAttribute(): string
    {
        return $this->short_name
            ? "{$this->short_name} ({$this->name})"
            : $this->name;
    }

    public function getStatusLabelAttribute(): string
    {
        return $this->status?->getLabel() ?? '-';
    }

    /**
     * Helper για απεικόνιση συμμετοχής στα στατιστικά
     */
    public function getCountingFlagsAttribute(): string
    {
        if ($this->is_virtual) {
            return 'Εικονική (χωρίς μέτρηση)';
        }

        $parts = [];

        if ($this->is_counted_in_lab) {
            $parts[] = 'Εργαστήριο';
        }

        if ($this->is_counted_in_contract) {
            $parts[] = 'Σύμβαση';
        }

        return !empty($parts)
            ? implode(' & ', $parts)
            : 'Δεν μετρά πουθενά';
    }
}
