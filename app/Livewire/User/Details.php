<?php

namespace App\Livewire\User;

use App\Models\Language;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\Candidate;

class Details extends Component
{
    public $modalOpen = false;

    public $candidate;

    public $details = false;

    public $career_break = "No";
    public $date_of_birth;
    public $marital_status = [];
    public $languages = [];

    public $allLanguages = [];
    public $address;
    public $hometown;
    public $pincode;

    protected $listeners =['openPdModal'];

    public function openPdModal(){
        $this->openModal();
    }

    public $user;
    public function mount($user)
    {
        $this->user = $user;
        $this->candidate = $this->user?->candidate;

        $allLanguages = Language::all();
        if ($this->candidate) {
            $this->details = true;
            $this->career_break = $this->candidate->career_break;
            $this->date_of_birth =$this->candidate->date_of_birth;
            $this->marital_status = $this->candidate->marital_status;
            $this->address = $this->candidate->address;
            $this->hometown = $this->candidate->hometown;
            $this->pincode = $this->candidate->pincode;
            $this->languages = $this->candidate->languages->pluck('id')->toArray();
        }
    }

    public function openModal()
    {
        $this->modalOpen = true;
        $this->dispatch('openSelect2');
    }

    public function closeModal()
    {
        $this->modalOpen = false;
    }

    public function save()
    {
        $this->dispatch('openSelect2');
        $this->validate([
            'career_break' => 'required',
            'date_of_birth' => 'required|date',
            'marital_status' => 'required',
            'languages' => 'required|array',
            'languages.*' => 'exists:languages,id',
            'address'      => 'required|string',
            'hometown'     => 'required|string',
            'pincode'      => 'required|numeric',
        ]);

        // dd('sdfs');
        $user = $this->user;

        $candidate = $user->candidate()->firstOrCreate([
            'user_id' => $user->id
        ]);

        $candidate->career_break = $this->career_break;
        $candidate->date_of_birth = $this->date_of_birth;
        $candidate->marital_status = $this->marital_status;
        $candidate->address = $this->address;
        $candidate->hometown = $this->hometown;
        $candidate->pincode = $this->pincode;

        if($this->languages){
            $candidate->languages()->sync($this->languages);
        }
        $candidate->save();

        $this->details =true;

        session()->flash('success', 'Personal details saved successfully.');

        $this->closeModal();
    }

    public function render()
    {
        return view('livewire.user.details');
    }
}
