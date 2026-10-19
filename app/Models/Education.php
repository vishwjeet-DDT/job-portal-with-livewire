<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Education extends Model
{
    protected $fillable = [
        "user_id",
        "course",
        "university",
        "course_type",
        "from_year",
        "to_year"
    ];
}
