<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Industry extends Model
{
     use HasFactory;
    protected $fillable = ['industry_types_id','name'];

    public function industryType()
    {
        return $this->belongsTo(IndustryType::class, 'industry_types_id');
    }
}
