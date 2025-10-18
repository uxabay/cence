<?php

namespace App\Models;

use App\Enums\ContractRevisionTypeEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class ContractRevision extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $table = 'contract_revisions';

    protected $fillable = [
        'contract_id',
        'revision_number',
        'revision_date',
        'type',
        'description',
        'amount_change',
        'new_total_value',
        'file_attachment_id',
        'remarks',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'revision_date' => 'date',
        'amount_change' => 'decimal:2',
        'new_total_value' => 'decimal:2',
        'type' => ContractRevisionTypeEnum::class,
    ];

    /*
    |--------------------------------------------------------------------------
    | Boot
    |--------------------------------------------------------------------------
    */
    protected static function boot()
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

    /*
    |--------------------------------------------------------------------------
    | Activity Log
    |--------------------------------------------------------------------------
    */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('contract.revision')
            ->logFillable()
            ->setDescriptionForEvent(fn (string $eventName) => "Contract revision has been {$eventName}");
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */
    public function contract()
    {
        return $this->belongsTo(Contract::class);
    }

    public function reallocations()
    {
        return $this->hasMany(ContractSampleReallocation::class);
    }

    public function fileAttachment()
    {
        return $this->belongsTo(FileAttachment::class, 'file_attachment_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */
    public function getChangeLabelAttribute(): string
    {
        return $this->amount_change >= 0
            ? '+' . number_format($this->amount_change, 2, ',', '.') . ' €'
            : number_format($this->amount_change, 2, ',', '.') . ' €';
    }

    public function getNewTotalFormattedAttribute(): string
    {
        return number_format($this->new_total_value, 2, ',', '.') . ' €';
    }
}
