<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContractSampleLabCategory extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $table = 'contract_sample_lab_category';

    protected $fillable = [
        'contract_sample_id',
        'lab_sample_category_id',
        'unit_price',
        'active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'active' => 'boolean',
        'unit_price' => 'decimal:2',
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
            ->useLogName('contract.sample_lab_category')
            ->logFillable()
            ->setDescriptionForEvent(fn (string $eventName) => "Contract sample lab category record has been {$eventName}");
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */
    public function contractSample(): BelongsTo
    {
        return $this->belongsTo(ContractSample::class, 'contract_sample_id');
    }

    public function labCategory(): BelongsTo
    {
        return $this->belongsTo(LabSampleCategory::class, 'lab_sample_category_id');
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
        return $query->where('active', true);
    }

    public function scopeInactive($query)
    {
        return $query->where('active', false);
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors & Helpers
    |--------------------------------------------------------------------------
    */
    public function getDisplayLabelAttribute(): string
    {
        $category = $this->labCategory?->name ?? '-';
        $price = $this->unit_price ? number_format($this->unit_price, 2) . ' €' : '-';

        return "{$category} ({$price})";
    }

    public function getActiveLabelAttribute(): string
    {
        return $this->active ? 'Ενεργή' : 'Ανενεργή';
    }
}
