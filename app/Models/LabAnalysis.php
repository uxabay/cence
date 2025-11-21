<?php

namespace App\Models;

use App\Enums\RecordStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class LabAnalysis extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $table = 'lab_analyses';

    protected $fillable = [
        'lab_sample_category_id',
        'name',
        'description',
        'unit_price',
        'currency_code',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'status' => RecordStatusEnum::class,
    ];

    protected static ?string $title = 'name';

    public function getFilamentRecordTitle(): string
    {
        return $this->name;
    }

    public function getRecordLabel(): ?string
    {
        return "{$this->name} ({$this->unit_price} €)";
    }

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
            ->useLogName('lab.analysis')
            ->logFillable()
            ->setDescriptionForEvent(fn (string $eventName) => "Lab analysis record has been {$eventName}");
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */
    public function labSampleCategory(): BelongsTo
    {
        return $this->belongsTo(LabSampleCategory::class, 'lab_sample_category_id');
    }

    public function registrations(): BelongsToMany
    {
        return $this->belongsToMany(Registration::class, 'registration_analysis')
            ->withPivot(['analysis_name', 'analysis_price'])
            ->withTimestamps();
    }

    public function labAnalysisPackages(): BelongsToMany
    {
        return $this->belongsToMany(
            LabAnalysisPackage::class,
            'lab_analysis_package_analysis'
        )->withPivot(['analysis_name', 'analysis_price'])
        ->withTimestamps();
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
}
