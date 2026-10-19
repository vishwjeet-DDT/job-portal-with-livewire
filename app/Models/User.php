<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Carbon\Carbon;


class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable,HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */

    const INDUSTRY_TYPES = ['it' => 'IT', 'non_it' => 'Non-IT'];

    const GENDER = ['male' => 'Male', 'female' => 'Female','other' =>'Other'];
    const EXPERIENCE_TYPES = ['experienced' => 'Experienced', 'fresher' => 'Fresher'];
    const STATUSES = ['active' => 'Active', 'inactive' => 'Inactive'];

    protected $fillable = [
        'name',
        'first_name',
        'last_name',
        'email',
        'profile_img',
        'mobile_number',
        'position',
        'country_id',
        'state_id',
        'city_id',
        'gender',
        'industry_type',
        'experience_type',
        'status',
        'password'
    ];



    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

     public function setPasswordAttribute($value)
    {
        if (!empty($value)) {
            $this->attributes['password'] = bcrypt($value);
        }
    }

    /**
     * Optional: get the user's full name.
     */
    public function setNameAttribute($value)
    {
        // If first_name or last_name are being set separately
        if (!empty($this->first_name) || !empty($this->last_name)) {
            $this->attributes['name'] = trim(($this->first_name ?? '') . ' ' . ($this->last_name ?? ''));
        }
        // If only name is provided, split it into first_name and last_name
        else {
            $this->attributes['name'] = $value;

            $parts = explode(' ', trim($value), 2);
            $this->attributes['first_name'] = $parts[0] ?? '';
            $this->attributes['last_name'] = $parts[1] ?? '';
        }
    }


    public function employer()
    {
        return $this->hasOne(Employer::class);
    }

    public function applications()
    {
        return $this->hasMany(JobApplication::class);
    }

    public function appliedJobs()
    {
        return $this->belongsToMany(Job::class, 'job_applications')
                    ->withPivot('status')
                    ->withTimestamps();
    }


    public function skills()
    {
        return $this->morphToMany(Skill::class, 'skillable')
            ->withPivot('experience_years', 'experience_months')
            ->withTimestamps();
    }

    public function getSkillNameAttribute()
    {
        return $this->skills->pluck('name')->implode(', ');
    }


    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function state()
    {
        return $this->belongsTo(State::class);
    }

    public function city(){
        return $this->belongsTo(City::class);
    }

    public function candidate()
    {
        return $this->hasOne(Candidate::class);
    }

    public function languages()
    {
        return $this->morphToMany(Language::class, 'languageable');
    }

    public function educations()
    {
        return $this->hasMany(Education::class);
    }

    public function employments()
    {
        return $this->hasMany(Employment::class);
    }

    public function getTotalExperienceAttribute()
    {
        $totalMonths = $this->employments->sum(function ($employment) {
            $start = Carbon::parse($employment->joining_date);
            $end = $employment->end_date
                ? Carbon::parse($employment->end_date)
                : Carbon::parse($employment->created_at);
            return $start->diffInMonths($end);
        });
        $years  = intdiv($totalMonths, 12);
        $months = $totalMonths % 12;

        return "{$years} Years {$months} Months";
    }


    public function currentEmployment()
    {
        return $this->employments()->where('is_current','yes');
    }

    public function getJobTitleAttribute()
    {
        return $this->employments->pluck('job_title')->implode(', ') ?? null;
    }

    public function notes()
    {
        return $this->morphMany(Note::class, 'noteable');
    }

}
