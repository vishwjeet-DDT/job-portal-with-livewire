<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FuncationalArea extends Model
{
    protected $fillable = ['industry_id', 'name'];

    public function industry()
    {
        return $this->belongsTo(Industry::class);
    }

}
