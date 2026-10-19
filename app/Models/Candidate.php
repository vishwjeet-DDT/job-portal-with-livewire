<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Candidate extends Model
{
        protected $fillable = [
        'user_id',
        'resume',
        'availability',
        'summary',
        'hometown',
        'pincode',
        'address',
        'date_of_birth',
        'career_break',
        'marital_status'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function languages()
    {
        return $this->morphToMany(Language::class, 'languageable');
    }
}
