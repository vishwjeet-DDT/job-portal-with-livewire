<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class City extends Model
{
     use HasFactory;

    protected $fillable = [
        "name",
        "state_id",
        "latitude",
        "longitude",
    ];


    public function state() { return $this->belongsTo(State::class); }

    public function jobs()
    {
        return $this->morphedByMany(Job::class, 'cityable');
    }

}
