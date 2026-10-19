<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Skill extends Model
{
    protected $fillable = ['name'];

    public function jobs()
    {
        return $this->morphedByMany(Job::class, 'skillable');
    }

    public function candidates()
    {
        return $this->morphedByMany(Candidate::class, 'skillable');
    }

    // public function companies()
    // {
    //     return $this->morphedByMany(Company::class, 'skillable');
    // }
}
