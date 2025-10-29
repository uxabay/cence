<?php

namespace App\Models;

use App\Enums\CustomerStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LabCustomer extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $table = 'lab_customers';

    protected $fillable = [
        'name',
        'customer_category_id',
        'contact_person',
        'phone',
        'address',
        'city',
        'postal_code',
        'tax_id',
        'organization_code',
        'email_primary',
        'encryption_key',
        'last_update_at',
        'status',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'status' => CustomerStatusEnum::class,
        'last_update_at' => 'datetime',
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
            ->useLogName('lab.customer')
            ->logFillable()
            ->setDescriptionForEvent(fn(string $eventName) => "Customer record has been {$eventName}");
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */
    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class, 'lab_customer_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(CustomerCategory::class, 'customer_category_id');
    }

    public function emails(): HasMany
    {
        return $this->hasMany(LabCustomerEmail::class, 'lab_customer_id');
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
        return $query->where('status', CustomerStatusEnum::Active);
    }

    public function scopeInactive($query)
    {
        return $query->where('status', CustomerStatusEnum::Inactive);
    }

    public function scopeArchived($query)
    {
        return $query->where('status', CustomerStatusEnum::Archived);
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    /**
     * Μετρά πόσοι πελάτες ανήκουν σε μια κατηγορία.
     */
    public static function countByCategory(int $categoryId): int
    {
        return static::where('customer_category_id', $categoryId)->count();
    }

    /**
     * Επιστρέφει το κύριο email (primary) — είτε από το πεδίο cache είτε από τη σχέση.
     */
    public function getPrimaryEmailAttribute(): ?string
    {
        if (!empty($this->email_primary)) {
            return $this->email_primary;
        }

        return $this->emails()
            ->where('is_primary', true)
            ->value('email');
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */
    public function getStatusLabelAttribute(): string
    {
        return $this->status?->getLabel() ?? '-';
    }

    public function getFullAddressAttribute(): string
    {
        $parts = array_filter([
            $this->address,
            $this->postal_code,
            $this->city,
        ]);

        return implode(', ', $parts);
    }
}
