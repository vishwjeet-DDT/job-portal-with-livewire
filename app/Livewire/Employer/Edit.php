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

class Edit extends Component
{
    use WithFileUploads;

    public $employerId;
    public $ceo_name, $company_name, $logo, $old_logo;
    public $industry_id, $functional_area_id, $ownership_type;
    public $company_size, $established_year, $location, $second_office_location;
    public $description, $website, $facebook_url, $linkedin_url;
    public $industries = [];
    public $states = [];
    public $cities = [];
    public $functionalAreas = [];
    public $user = [
        'name' => '',
        'email' => '',
        'mobile_number' => '',
        'country_id' => '',
        'state_id' => '',
        'city_id' => '',
    ];

    protected $rules = [
        'user.name' => 'required|string|max:255',
        'user.email' => 'required|email',
        'user.country_id' => 'required|exists:countries,id',
        'user.state_id' => 'required|exists:states,id',
        'user.city_id' => 'required|exists:cities,id',
        'user.mobile_number' =>'required',
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

    public function mount($id)
    {
        $this->employerId = $id;
        $employer = Employer::with('user')->findOrFail($id);

        $this->ceo_name = $employer->ceo_name;
        $this->company_name = $employer->company_name;
        $this->industry_id = $employer->industry_id;
        $this->functional_area_id = $employer->functional_area_id;
        $this->ownership_type = $employer->ownership_type;
        $this->company_size = $employer->company_size;
        $this->established_year = $employer->established_year;
        $this->location = $employer->location;
        $this->second_office_location = $employer->second_office_location;
        $this->description = $employer->description;
        $this->website = $employer->website;
        $this->facebook_url = $employer->facebook_url;
        $this->linkedin_url = $employer->linkedin_url;
        $this->old_logo = $employer->logo;

        // Prefill user data
        $this->user = [
            'name' => $employer->user->name,
            'email' => $employer->user->email,
            'mobile_number' => $employer->user->mobile_number,
            'country_id' => $employer->user->country_id,
            'state_id' => $employer->user->state_id,
            'city_id' => $employer->user->city_id,
        ];

        // Load selects
        $this->industries = Industry::all();
        $this->states = State::where('country_id', $this->user['country_id'])->get();
        $this->cities = City::where('state_id', $this->user['state_id'])->get();
        $this->functionalAreas = FuncationalArea::where('industry_id', $this->industry_id)->get();
    }

    public function updatedIndustryId($value)
    {
        $this->functionalAreas = FuncationalArea::where('industry_id', $value)->get();
        $this->dispatch('refresh-select2');
    }

    public function updatedUserCountryId($value)
    {
        $this->states = State::where('country_id', $value)->get();
        $this->user['state_id'] = '';
        $this->cities = [];
        $this->user['city_id'] = '';
        $this->dispatch('refresh-select2');
    }

    public function updatedUserStateId($value)
    {
        $this->cities = City::where('state_id', $value)->get();
        $this->user['city_id'] = '';
        $this->dispatch('refresh-select2');
    }
    
    public function updatedLogo($value)
    {
        $this->dispatch('refresh-select2');
    }

    public function update()
    {
        $this->validate();

        $employer = Employer::findOrFail($this->employerId);
        $user = $employer->user;

        // Update logo
        if ($this->logo) {
            // $path = $this->logo->store('employers/logo', 'public');
            // $employer->logo = $path;
            
             $directory = public_path('storage/employers/logo');
            if (!file_exists($directory)) {
                mkdir($directory, 0755, true);
            }
            $filename = time() . '_' . $this->logo->getClientOriginalName();
            $tempPath = $this->logo->getRealPath();
            $destinationPath = $directory . '/' . $filename;
            copy($tempPath, $destinationPath);
            $employer->logo = 'employers/logo/' . $filename;
        }

        // Update employer fields
        $employer->update([
            'ceo_name' => $this->ceo_name,
            'company_name' => $this->company_name,
            'industry_id' => $this->industry_id,
            'functional_area_id' => $this->functional_area_id,
            'ownership_type' => $this->ownership_type,
            'company_size' => $this->company_size,
            'established_year' => $this->established_year,
            'location' => $this->location,
            'second_office_location' => $this->second_office_location,
            'description' => $this->description,
            'website' => $this->website,
            'facebook_url' => $this->facebook_url,
            'linkedin_url' => $this->linkedin_url,
        ]);

        // Update user fields
        $user->update($this->user);

        $this->dispatch('success', message: 'Employer updated successfully!');
    }

    public function render()
    {
        return view('livewire.employer.edit');
    }
}
