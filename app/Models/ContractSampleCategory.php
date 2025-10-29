<?php

namespace App\Models;

use App\Enums\RecordStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Illuminate\Support\Facades\Auth;

class ContractSampleCategory extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $table = 'contract_sample_categories';

    protected $fillable = [
        'code',
        'name',
        'description',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'status' => RecordStatusEnum::class,
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
            ->useLogName('contract.sample_category')
            ->logFillable()
            ->setDescriptionForEvent(fn (string $eventName) => "Contract sample category has been {$eventName}");
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
        return $this->code
            ? "{$this->code} – {$this->name}"
            : $this->name;
    }
}
