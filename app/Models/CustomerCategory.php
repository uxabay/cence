<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Enums;

class CustomerCategory extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $table = 'customer_categories';

    protected $fillable = [
        'name',
        'description',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'status' => \App\Enums\CustomerCategoryStatus::class,
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
            ->useLogName('lab.customer_category')
            ->logFillable()
            ->setDescriptionForEvent(fn(string $eventName) => "Customer category has been {$eventName}");
    }

    /**
     * Σχέσεις
     */
    public function customers(): HasMany
    {
        return $this->hasMany(LabCustomer::class, 'customer_category_id');
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
     * Accessor: Επιστρέφει τον αριθμό πελατών στην κατηγορία
     */
    public function getCustomersCountAttribute(): int
    {
        return $this->customers()->count();
    }

    /**
     * Scope για ενεργές κατηγορίες
     */
    public function scopeActive($query)
    {
        return $query->where('status', \App\Enums\CustomerCategoryStatus::Active);
    }

    /**
     * Business rule: Μια κατηγορία πελατών δεν διαγράφεται αν έχει συνδεδεμένους πελάτες.
     */
    public function canBeDeleted(): bool
    {
        return ! $this->customers()->exists();
    }

    public function deletionBlockers(): array
    {
        $blockers = [];

        if ($this->customers()->exists()) {
            $blockers[] = 'customers';
        }

        return $blockers;
    }

}
