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

    /**
     * Πίνακας βάσης δεδομένων
     */
    protected $table = 'lab_customers';

    /**
     * Μαζικά ενημερώσιμα πεδία
     */
    protected $fillable = [
        'name',
        'customer_category_id',
        'contact_person',
        'phone',
        'address',
        'city',
        'postal_code',
        'encryption_key',
        'last_update_at',
        'status',
        'notes',
        'created_by',
        'updated_by',
    ];

    /**
     * Τύποι δεδομένων
     */
    protected $casts = [
        'status' => CustomerStatusEnum::class,
        'last_update_at' => 'datetime',
    ];

    /**
     * Αυτόματη συμπλήρωση audit πεδίων
     */
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

    /**
     * Activity Log configuration
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('lab.customer')
            ->logFillable()
            ->setDescriptionForEvent(fn(string $eventName) => "Customer record has been {$eventName}");
    }

    /**
     * Σχέσεις
     */
    public function registrations()
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

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('status', CustomerStatusEnum::Active);
    }

    public function scopeInactive($query)
    {
        return $query->where('status', CustomerStatusEnum::Inactive);
    }

    /**
     * Helper για μέτρηση πελατών ανά κατηγορία
     * Παράδειγμα: LabCustomer::countByCategory($categoryId)
     */
    public static function countByCategory(int $categoryId): int
    {
        return static::where('customer_category_id', $categoryId)->count();
    }

    /**
     * Accessors
     */

    // Επιστρέφει την ελληνική ετικέτα του status (π.χ. Ενεργός / Ανενεργός)
    public function getStatusLabelAttribute(): string
    {
        return $this->status?->getLabel() ?? '-';
    }

    // Επιστρέφει συγκεντρωτικά τη διεύθυνση σε μία γραμμή
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
