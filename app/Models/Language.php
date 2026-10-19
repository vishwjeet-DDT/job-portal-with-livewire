<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Language extends Model
{
    protected $fillable = ['name'];

    public function jobs()
    {
        return $this->morphedByMany(Job::class, 'languageable');
    }

    // public function candidates()
    // {
    //     return $this->morphedByMany(Candidate::class, 'languageable');
    // }

    // public function companies()
    // {
    //     return $this->morphedByMany(Company::class, 'languageable');
    // }
}
