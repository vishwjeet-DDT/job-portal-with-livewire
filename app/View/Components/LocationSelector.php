<?php
namespace App\View\Components;

use App\Models\Country;
use Illuminate\View\Component;

class LocationSelector extends Component
{
    public $countryId;
    public $stateId;
    public $cityId;
    public $mobileNumber;
    public $col;

    public function __construct($countryId = null, $stateId = null, $cityId = null, $mobileNumber = null,$col=null)
    {
        $this->countryId = $countryId;
        $this->stateId = $stateId;
        $this->cityId = $cityId;
        $this->mobileNumber = $mobileNumber;
        $this->col = $col;
    }

    public function render()
    {
        $countries = Country::select('id', 'name')->orderBy('name')->get();
        return view('components.location-selector', compact('countries'));
    }
}
