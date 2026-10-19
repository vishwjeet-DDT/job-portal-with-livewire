<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;

class JobApplication extends Model
{

    public const STATUS_COLORS = [
        'pending'   => 'warning',
        'approved'  => 'success',
        'rejected'  => 'danger',
        'scheduled' => 'info',
        'selected'  => 'primary'
    ];
    protected $fillable = [
        'job_id', 'user_id','status','interview_date','updated_by'
    ];

    public function job()
    {
        return $this->belongsTo(Job::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function notes()
    {
        return $this->morphMany(Note::class, 'noteable');
    }

    public function getStatusColorAttribute(): string
    {
        return self::STATUS_COLORS[$this->status] ?? 'secondary';
    }

    public function updateBy()
    {
        return $this->belongsTo(User::class,'updated_by');
    }
}
