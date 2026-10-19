<?php

namespace App\Livewire\User;

use App\Models\{State,City};
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;

class Profile extends Component
{

    use WithFileUploads;

    public $user;

    // Profile Photo
    public $photo;
    public $states = [];
    public $cities = [];

    public $country_id = 101,
            $state_id = 4037,
            $city_id = 57766;

    // Edit Basic Details
    public $first_name, $last_name, $mobile_number, $email;
    public $country, $state, $city;
    public $availability, $gender, $industry_type;
    public $experience_type;

    public $showEditModal = false;

    protected $listeners =['openEditModal'];

    public function mount($user)
    {
        $this->user = $user;
        $this->states = State::where('country_id', $this->user->country_id)->get();
        // dd($this->states);

        $this->cities = City::where('state_id', $this->user->state_id)->get();
        $this->setFormData();
    }

    public function updatedCountryId($value)
    {
        // dd('sdfsd');
        $this->states = State::where('country_id', $value)->get();
        $this->user['state_id'] = '';
        $this->cities = [];
        $this->user['city_id'] = '';
        // $this->dispatch('refresh-select2');
    }

    public function updatedStateId($value)
    {
        $this->cities = City::where('state_id', $value)->get();
        $this->user['city_id'] = '';
        // $this->dispatch('refresh-select2');
    }

    public function setFormData()
    {
        $this->first_name = $this->user->first_name;
        $this->last_name = $this->user->last_name;
        $this->mobile_number = $this->user->mobile_number;
        $this->email = $this->user->email;
        $this->country_id = $this->user->country_id;
        $this->state_id = $this->user->state_id;
        $this->city_id = $this->user->city_id;
        $this->availability = $this->user->candidate?->availability;
        $this->gender = $this->user->gender;
        $this->industry_type = $this->user->industry_type;
        $this->experience_type = $this->user->experience_type;
    }

    public function openEditModal()
    {
        $this->showEditModal = true;
        // dd('sdfs');
    }

    public function saveProfile()
    {
        $this->validate([
            'first_name' => 'required',
            'last_name'  => 'required',
            'mobile_number'     => 'required',
            'email'      => 'required|email',
            'country_id'    => 'required',
            'state_id'      => 'required',
            'city_id'       => 'required',
            'gender'     => 'required',
            'availability' => 'required',
            'industry_type'    => 'required',
            'experience_type' => 'required',
        ]);

        // dd($this);
        $this->user->update([
            'first_name' => $this->first_name,
            'last_name'  => $this->last_name,
            'name' => $this->first_name.' '.$this->last_name,
            'mobile_number'     => $this->mobile_number,
            'email'      => $this->email,
            'country_id'    => $this->country_id,
            'state_id'      => $this->state_id,
            'city_id'       => $this->city_id,
            'gender'     => $this->gender,
            'industry_type'    => $this->industry_type,
            'experience_type' => $this->experience_type,
        ]);

        if($this->availability)
        {
            $user = $this->user;
            $candidate = $user->candidate()->firstOrCreate([
                'user_id' => $user->id
            ]);

            $candidate->availability = $this->availability;
            $candidate->save();
        }

        $this->showEditModal = false;
        // $this->user = auth()->user();

        session()->flash('success', 'Profile updated successfully.');
    }

    public function updatedPhoto()
    {
        $this->validate([
            'photo' => 'image|max:2048'
        ]);
        
        
         $directory = public_path('storage/profile_img');
        if (!file_exists($directory)) {
            mkdir($directory, 0755, true);
        }
        $filename = time() . '_' . $this->photo->getClientOriginalName();
        $tempPath = $this->photo->getRealPath();
        $destinationPath = $directory . '/' . $filename;
        copy($tempPath, $destinationPath);
        $this->user->profile_img = 'profile_img/' . $filename;
        $this->user->save();
    }
    public function render()
    {
        return view('livewire.user.profile');
    }
}
