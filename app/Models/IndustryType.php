<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IndustryType extends Model
{
    protected $fillable = ['name'];

    public function industries()
    {
        return $this->hasMany(Industry::class, 'industry_types_id');
    }
}
