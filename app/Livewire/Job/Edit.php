<?php

namespace App\Livewire\Job;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Traits\HasDynamicForm;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use App\Models\
{
    Industry,
    FuncationalArea,
    Country,
    State,
    City,
    User,
    Job,
    Employer,
    IndustryType,
    Language,
    Skill
};

class Edit extends Component
{

    use HasDynamicForm;
    public $job;
    public $industries = [];
    public $industry_types = [];
    public $functional_areas = [];

    public $states = [];
    public $cities = [];
    public $skills = [];
    public function mount($id)
    {
        $this->job = Job::find($id);
        $this->industry_types = IndustryType::pluck('name','id')->toArray();
        $this->states = State::where('country_id',$this->job?->country_id)->pluck('name','id')->toArray();
        $this->cities = City::where('state_id',$this->job?->state_id)->pluck('name','id')->toArray();

        $this->skills = Skill::pluck('name', 'id')->toArray();

        $this->industries = Industry::where('industry_types_id',$this->job?->industry_type_id)->pluck('name','id')->toArray();

        $this->functional_areas = FuncationalArea::where('industry_id',$this->job?->industry_id)->pluck('name','id')->toArray();


        if($this->job->cities)
        {
            $this->job->city_id = $this->job->cities->pluck('id');
        }

        if($this->job->getLanguages)
        {
            $this->job->languages = $this->job->getLanguages->pluck('id');
        }

        if($this->job->skills)
        {
            $this->job->skill = $this->job->skills->pluck('id');
        }
        $this->buildForm();

        if($this->job)
        {
            $this->formData = $this->job->toArray();
        }
    }

    private function buildForm()
    {
        $this->initializeFormFields(
            tableName: 'jobs',
            selectOptions: [
                'country_id' => Country::pluck('name', 'id')->toArray(),
                'industry_type_id' => $this->industry_types,
                'languages' => Language::pluck('name','id')->toArray(),
                'skill' => $this->skills,
                'industry_id' => $this->industries,
                'funcational_area_id' => $this->functional_areas,
                'state_id'   => $this->states ?? [],
                'city_id'    => $this->cities ?? [],
                'work_mode' => Job::WORK_MODE,
                'gender' => User::GENDER,
                'employment_type' => Job::EMPLOYEMENT_TYPE,
                'employer_id' => Employer::pluck('company_name', 'id')->toArray(),
                'status' => Job::STATUSES,
                'shift' => Job::SHIFT_TYPES
            ],
            excludeColumns: ['slug']
        );

        $this->dispatch('refresh-select3');
    }

    public function updatedFormDataIndustryTypeId($value)
    {
        $this->industries = Industry::where('industry_types_id',$value)->pluck('name','id')->toArray();

        $firstIndustryId = array_key_first($this->industries);

        $this->functional_areas = FuncationalArea::where('industry_id', $firstIndustryId)
            ->pluck('name', 'id')
            ->toArray();
        $this->buildForm();

    }

    public function updatedFormDataIndustryId($value)
    {

        $this->functional_areas = FuncationalArea::where('industry_id', $value)
            ->pluck('name', 'id')
            ->toArray();
        $this->buildForm();
    }

     public function updatedFormDataCountryId($value)
    {
        $this->states = State::where('country_id', $value)->pluck('name', 'id')->toArray();
        $this->state_id = null;
        $this->cities = [];
        $this->city_id = null;
        $this->buildForm();
    }
    public function updatedFormDataStateId($value)
    {
        $this->cities = City::where('state_id', $value)->pluck('name', 'id')->toArray();
        $this->city_id = null;
        $this->buildForm();
    }

    public function rules()
    {
        $rules = [];
        $rules = [
        'formData.country_id' => 'required|exists:countries,id',
        'formData.state_id' => 'required|exists:states,id',
        'formData.city_id' => 'required|exists:cities,id',
        'formData.gender' => ['required', Rule::in(User::GENDER)],
        'formData.industry_type_id' => 'required|exists:industry_types,id',
         'formData.skill'   => ['required', 'array', 'min:1'],
        'formData.skill.*' => ['required', 'integer', 'exists:skills,id'],
        'formData.languages'   => ['required', 'array', 'min:1'],
        'formData.languages.*' => ['required', 'integer', 'exists:languages,id'],
        ];
        return array_merge($rules,
        $this->getValidationRules());
    }

    public function save()
    {
        $this->dispatch('refresh-select3');
        $this->validate();
        $data = $this->formData;
        // dd($this->formData['city_id'], gettype($this->formData['city_id']));
        $cityIds = array_first((array) $this->formData['city_id']);
        $languages = array_first((array) $this->formData['languages']);
        $skill = array_first((array) $this->formData['skill']);


        // dd(array_first($cityIds)[0]);
        $data['city_id'] = array_first($cityIds);
        $data['skill'] = array_first($skill);
        $data['languages'] = array_first($languages);
        $this->job->update($data);

        $job = $this->job;

        $job->cities()->sync($cityIds);
        $job->skills()->sync($skill);
        $job->getLanguages()->sync($languages);

        $job->created_by = auth()->user()->id;
        $job->save();
        $this->dispatch('success', message: 'Job Update successfully!');
    }

    public function render()
    {
        return view('livewire.job.edit');
    }
}
