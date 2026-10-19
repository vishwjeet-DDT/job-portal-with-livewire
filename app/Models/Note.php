<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Note extends Model
{
    protected $fillable = ['note','created_by'];

    public function noteable()
    {
        return $this->morphTo();
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class,'created_by');
    }

    public function getAttributeCreatedByName()
    {
        return $this->createdBy?->name ?? 'N/A';
    }
}
