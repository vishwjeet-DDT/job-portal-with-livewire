<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Country extends Model
{
     use HasFactory;

    protected $fillable = [
        "name",
        "iso2",
        "phonecode",
        "latitude",
        "longitude",
    ];

    public function states() {
          return $this->hasMany(State::class);
        }
}
