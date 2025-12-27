<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class RegistrationAnalysis extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $table = 'registration_analysis';

    // Πρέπει να υπάρχει id στο pivot σύμφωνα με το Filament
    public $incrementing = true;

    protected $fillable = [
        'registration_id',
        'lab_analysis_id',
        'analysis_name',
        'analysis_price',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'analysis_price' => 'decimal:2',
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
            ->useLogName('registration.analysis')
            ->logFillable()
            ->setDescriptionForEvent(fn(string $eventName) =>
                "Registration analysis record has been {$eventName}"
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */
    public function registration(): BelongsTo
    {
        return $this->belongsTo(Registration::class, 'registration_id');
    }

    public function labAnalysis(): BelongsTo
    {
        return $this->belongsTo(LabAnalysis::class, 'lab_analysis_id');
    }
}
