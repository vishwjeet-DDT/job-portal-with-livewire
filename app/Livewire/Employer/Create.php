<?php

namespace App\Livewire\Employer;

use App\Models\User;
use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Industry;
use App\Models\FuncationalArea;
use App\Models\Country;
use App\Models\State;
use App\Models\City;
use App\Models\Employer;

class Create extends Component
{
    use WithFileUploads;

    public $ceo_name, $company_name,$logo;
    public $industry_id, $functional_area_id, $ownership_type;
    public $company_size, $established_year, $location, $second_office_location;
    public $description, $website, $facebook_url, $linkedin_url;

    public $industries = [];
    public $states = [];
    public $cities = [];
    public $functionalAreas = [];
    public $user = [
            'country_id' => 101,
            'state_id'=>4021,
            'city_id' =>131821,
        ];



    protected $rules = [
        'user.name' => 'required|string|max:255',
        'user.email' => 'required|email|unique:users,email',
        'user.country_id' => 'required|exists:countries,id',
        'user.state_id' => 'required|exists:states,id',
        'user.city_id' => 'required|exists:cities,id',
        'user.mobile_number' =>'required',
        'logo' => 'required|image|mimes:jpg,jpeg,png,svg|max:2048',
        'ceo_name' => 'nullable|string|max:255',
        'company_name' => 'required|string|max:255',
        'industry_id' => 'required|exists:industries,id',
        'functional_area_id' => 'required|exists:funcational_areas,id',
        'ownership_type' => 'required|string',
        'company_size' => 'required',
        'established_year' => 'required|numeric',
        'location' => 'required|string',
        'description' => 'required|string',
        'website' => 'required|url',
        'facebook_url' => 'nullable|url',
        'linkedin_url' => 'nullable|url',
    ];

    public function mount()
    {
        $this->industries = Industry::all();
        $this->states = State::where('country_id', $this->user['country_id'])->get();
        $this->cities = City::where('state_id', $this->user['state_id'])->get();
        if ($this->industry_id) {
            $this->functionalAreas = FuncationalArea::where('industry_id', $this->industry_id)->get();
        }
    }

    public function updatedLogo($value)
    {
        $this->dispatch('refresh-select2');
    }
    public function updatedIndustryId($value)
    {
        $this->functionalAreas = FuncationalArea::where('industry_id', $value)->get();
        $this->functional_area = null;
        $this->dispatch('refresh-select2');
    }

    public function updatedUserCountryId($value)
    {
        $this->states = State::where('country_id', $value)->get();
        $this->state_id = null;
        $this->cities = [];
        $this->city_id = null;
        $this->dispatch('refresh-select2');
    }
    public function updatedUserStateId($value)
    {
        $this->cities = City::where('state_id', $value)->get();
        $this->city_id = null;
        $this->dispatch('refresh-select2');
    }

    public function submit()
    {
        $this->validate();
        $employerData = $this->except('user','industries','states','cities','functionalAreas');
        // dd($this->all());

        // $imagePath = $this->logo->store('employers/logo', 'public');
        // $employerData['logo'] = $imagePath;

        $directory = public_path('storage/employers/logo');
        if (!file_exists($directory)) {
            mkdir($directory, 0755, true);
        }
        $filename = time() . '_' . $this->logo->getClientOriginalName();
        $tempPath = $this->logo->getRealPath();
        $destinationPath = $directory . '/' . $filename;
        copy($tempPath, $destinationPath);
         $employerData['logo'] = 'employers/logo/' . $filename;

        $user = User::create($this->user);
        $user->employer()->create($employerData);

        $this->reset();

        $this->dispatch('reset-js-fields');

        $this->mount();

        $this->dispatch('success', message: 'Employer created successfully!');
    }

    public function render()
    {
        return view('livewire.employer.create');
    }
}
