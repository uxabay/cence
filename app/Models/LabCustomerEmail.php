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

class LabCustomerEmail extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    /**
     * Πίνακας βάσης δεδομένων
     */
    protected $table = 'lab_customer_emails';

    /**
     * Μαζικά ενημερώσιμα πεδία
     */
    protected $fillable = [
        'lab_customer_id',
        'email',
        'is_primary',
        'notes',
        'created_by',
        'updated_by',
    ];

    /**
     * Τύποι δεδομένων
     */
    protected $casts = [
        'is_primary' => 'boolean',
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
            ->useLogName('lab.customer_email')
            ->logFillable()
            ->setDescriptionForEvent(fn(string $eventName) => "Customer email record has been {$eventName}");
    }

    /**
     * Σχέσεις
     */
    public function customer()
    {
        return $this->belongsTo(LabCustomer::class, 'lab_customer_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
