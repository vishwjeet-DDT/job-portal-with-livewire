<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Country;
use App\Models\State;
use App\Models\City;

class LocationSelector extends Component
{
    public $mobileNumber = false;
    public $col = 6;
    public $componentId;

    public $countryId;
    public $stateId;
    public $cityId;
    public $countries = [];
    public $states = [];
    public $cities = [];

    public function mount($countryId = null, $stateId = null, $cityId = null, $mobileNumber = false, $col = 6)
    {
        $this->mobileNumber = $mobileNumber;
        $this->col = $col;
        $this->componentId = uniqid('location_');

        $this->countries = Country::orderBy('name')->get();
        $this->countryId = $countryId;
        $this->stateId = $stateId;
        $this->cityId = $cityId;

        if ($countryId) {
            $this->states = State::where('country_id', $countryId)->orderBy('name')->get();
        }

        if ($stateId) {
            $this->cities = City::where('state_id', $stateId)->orderBy('name')->get();
        }
    }

    public function updatedCountryId($value)
    {
        $this->stateId = null;
        $this->cityId = null;
        $this->states = $value ? State::where('country_id', $value)->orderBy('name')->get() : [];
        $this->cities = [];

        // Dispatch browser event to reinitialize select2
        $this->dispatch('reinitialize-selects', componentId: $this->componentId);
    }

    public function updatedStateId($value)
    {
        $this->cityId = null;
        $this->cities = $value ? City::where('state_id', $value)->orderBy('name')->get() : [];

        // Dispatch browser event to reinitialize select2
        $this->dispatch('reinitialize-selects', componentId: $this->componentId);
    }

    public function render()
    {
        return view('livewire.location-selector');
    }
}
