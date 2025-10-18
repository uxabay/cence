<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class FileAttachment extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $table = 'file_attachments';

    protected $fillable = [
        'attachable_type',
        'attachable_id',
        'filename',
        'original_name',
        'mime_type',
        'size',
        'path',
        'disk',
        'uploaded_by',
        'uploaded_at',
    ];

    protected $casts = [
        'uploaded_at' => 'datetime',
        'size' => 'integer',
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
                $model->uploaded_by = Auth::id();
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
            ->useLogName('file.attachment')
            ->logFillable()
            ->setDescriptionForEvent(fn (string $eventName) => "File attachment has been {$eventName}");
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */
    public function attachable()
    {
        return $this->morphTo();
    }

    public function uploadedBy()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors & Helpers
    |--------------------------------------------------------------------------
    */
    public function getUrlAttribute(): ?string
    {
        return Storage::disk($this->disk)->exists($this->path)
            ? Storage::disk($this->disk)->url($this->path)
            : null;
    }

    public function getFileSizeFormattedAttribute(): string
    {
        if ($this->size < 1024) {
            return $this->size . ' B';
        } elseif ($this->size < 1048576) {
            return round($this->size / 1024, 2) . ' KB';
        } else {
            return round($this->size / 1048576, 2) . ' MB';
        }
    }
}
