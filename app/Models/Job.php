<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Job extends Model
{
    const STATUSES = ['draft' => 'Draft', 'published' => 'Published','expired' => 'Expired'];

    const EMPLOYEMENT_TYPE = ['contract' => 'Contract', 'permanent' => 'Permanent','hourly' => 'Hourly'];

    const WORK_MODE = ['onsite' => 'Onsite', 'hybrid' => 'Hybrid','remote' => 'Remote'];

    const SHIFT_TYPES = [
                            'morning_shift'      => 'Morning Shift',
                            'evening_shift'      => 'Evening Shift',
                            'night_shift'        => 'Night Shift',
                            'full_time'          => 'Full-Time',
                            'part_time'          => 'Part-Time',
                            'rotational_shifts'  => 'Rotational Shifts',
                            'standby_on_call'    => 'Standby / On-Call',
                        ];


    protected $fillable =
    [
        'employer_id',
        'title',
        'slug',
        'employment_type',
        'work_mode',
        'gender',
        'skill',
        'languages',
        'industry_type_id',
        'industry_id',
        'funcational_area_id',
        'country_id',
        'state_id',
        'city_id',
        'shift',
        'min_salary',
        'max_salary',
        'currency',
        'description',
        'experience_level',
        'qualification',
        'number_of_vacancy',
        'deadline',
        'status'
    ];

    protected $appends = ['company_name'];

    public function getLanguages()
    {
        return $this->morphToMany(Language::class, 'languageable');
    }

    public function skills()
    {
        return $this->morphToMany(Skill::class, 'skillable');
    }

    public function cities()
    {
        return $this->morphToMany(City::class, 'cityable');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function state()
    {
        return $this->belongsTo(State::class);
    }

    public function applicants()
    {
        return $this->belongsToMany(User::class, 'job_applications')
                    ->withPivot(['id', 'status', 'interview_date'])
                    ->withTimestamps();
    }

    public function company(){
        return $this->belongsTo(Employer::class,'employer_id');
    }

    public function getCompanyNameAttribute()
    {
        return $this->company ? $this->company->company_name : '';
    }

    public function job_category()
    {
        return $this->belongsTo(FuncationalArea::class, 'funcational_area_id', 'id');
    }

}
