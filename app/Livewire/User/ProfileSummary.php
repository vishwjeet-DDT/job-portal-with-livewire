<?php

namespace App\Livewire\User;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class ProfileSummary extends Component
{
    public $summary;
    public $showModal = false;

    public $user;

    protected $listeners =['openPsModal'];

     public function openPSModal(){
        $this->openModal();
    }

    public function mount($user)
    {
        $this->user = $user;
        $this->summary = $user?->candidate ? $user?->candidate?->summary : null;
    }

    public function openModal()
    {
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
    }

    public function editModal()
    {
        $user = $this->user;
        $this->summary = $user?->candidate ? $user?->candidate?->summary : null;
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate([
            'summary' => 'required|min:5'
        ]);

        $user = $this->user;
        $candidate = $user->candidate()->firstOrCreate([
            'user_id' => $user->id
        ]);

        $candidate->summary = $this->summary;
        $candidate->save();

        session()->flash('success', 'Profile summary updated successfully.');

        $this->closeModal();
    }

    public function render()
    {
        return view('livewire.user.profile-summary');
    }
}
