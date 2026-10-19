<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employment extends Model
{
     protected $fillable = [
        "user_id",
        "company_name",
        "job_title",
        "joining_date",
        "is_current",
        "expected_notice",
        'end_date'
    ];
}
