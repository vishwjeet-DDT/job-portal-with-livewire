<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employer extends Model
{
     protected $fillable = [
        "user_id",
        "ceo_name",
        "company_name",
        "industry_id",
        "functional_area_id",
        "ownership_type",
        "company_size",
        "established_year",
        "location",
        "second_office_location",
        "description",
        "website",
        "facebook_url",
        "linkedin_url",
        "created_by",
        "status",
        'logo'

    ];

    public function industry()
    {
        return $this->belongsTo(Industry::class, 'industry_id');
    }

    public function funcationalArea(){
        return $this->belongsTo(FuncationalArea::class, 'functional_area_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
