<?php

namespace App\Models;

use App\Models\User;
use App\Models\LabCustomer;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LabCustomerEmail extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $table = 'lab_customer_emails';

    protected $fillable = [
        'lab_customer_id',
        'email',
        'is_primary',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | Boot events for audit fields
    |--------------------------------------------------------------------------
    */
    protected static function booted(): void
    {
        // 📌 Audit fields
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

        // 🟢 Διασφάλιση μοναδικού primary email ανά πελάτη
        static::saving(function ($email) {
            // Αν το email ΔΕΝ είναι primary, δεν χρειάζεται να κάνουμε κάτι
            if (! $email->is_primary) {
                return;
            }

            // Αν είναι primary, απενεργοποιούμε όλα τα υπόλοιπα του ίδιου πελάτη
            if ($email->lab_customer_id) {
                $email->customer
                    ->emails()
                    ->where('id', '!=', $email->id)
                    ->where('is_primary', true)
                    ->update(['is_primary' => false]);
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
            ->useLogName('lab.customer_email')
            ->logFillable()
            ->setDescriptionForEvent(fn (string $eventName) => "Customer email record has been {$eventName}");
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(LabCustomer::class, 'lab_customer_id');
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
    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }

    public function scopeForCustomer($query, int $customerId)
    {
        return $query->where('lab_customer_id', $customerId);
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors & Helpers
    |--------------------------------------------------------------------------
    */

    /**
     * Επιστρέφει αν το email είναι κύριο, με ανθρώπινη ετικέτα.
     */
    public function getPrimaryLabelAttribute(): string
    {
        return $this->is_primary ? 'Κύριο' : 'Δευτερεύον';
    }

    /**
     * Σύντομη περιγραφή για προβολή π.χ. σε πίνακα.
     */
    public function getDisplayLabelAttribute(): string
    {
        return $this->is_primary
            ? "{$this->email} (Κύριο)"
            : $this->email;
    }
}
