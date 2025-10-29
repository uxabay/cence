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
        'default_price',
        'currency_code',
        'method_ref',
        'standard_ref',
        'status',
        'sort_order',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'default_price' => 'decimal:2',
        'status' => RecordStatusEnum::class,
    ];

    /*
    |--------------------------------------------------------------------------
    | Boot
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
            ->useLogName('lab.sample_category')
            ->logFillable()
            ->setDescriptionForEvent(fn (string $eventName) => "Lab sample category has been {$eventName}");
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */
    public function lab(): BelongsTo
    {
        return $this->belongsTo(Lab::class, 'lab_id');
    }

    public function sampleType(): BelongsTo
    {
        return $this->belongsTo(SampleType::class, 'sample_type_id');
    }

    public function registrations()
    {
        return $this->hasMany(Registration::class, 'lab_sample_category_id');
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
        return $query->where('status', RecordStatusEnum::Active);
    }

    public function scopeInactive($query)
    {
        return $query->where('status', RecordStatusEnum::Inactive);
    }

    public function scopeByLab($query, int $labId)
    {
        return $query->where('lab_id', $labId);
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors & Helpers
    |--------------------------------------------------------------------------
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
     * Επιστρέφει την τιμή για τη συγκεκριμένη ημερομηνία.
     * Προς το παρόν επιστρέφει πάντα την default τιμή.
     */
    public function getPriceForDate(?string $date = null): float
    {
        return (float) $this->default_price;
    }

    /**
     * Εμφανιστική ετικέτα (code + name)
     */
    public function getDisplayLabelAttribute(): string
    {
        return $this->code
            ? "{$this->code} – {$this->name}"
            : $this->name;
    }
}
