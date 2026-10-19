<?php

namespace App\Livewire\User;

use Livewire\Component;
use App\Models\Skill as userSkill;
use Auth;

class Skill extends Component
{
    public $skills;
    public $userSkills;

    public $skill_id = null, $experience_years= null, $experience_months= null;
    public $newSkill;
    public $editId = null;
    public $modalOpen =false;
    public $user;

    protected $listeners = ['openSkillModal','closeModel'];

    public function openSkillModal()
    {
        $this->resetForm();
        $this->modalOpen = true;
    }

    public function closeModel()
    {
        $this->modalOpen = false;
    }

    public function mount($user)
    {
        $this->user = $user;
        $this->loadSkills();
        $this->loadUserSkills();
    }

    public function loadSkills()
    {
        $this->skills = userSkill::orderBy('name')->get();
    }

    public function loadUserSkills()
    {
        $this->userSkills = $this->user?->skills ? $this->user
            ->skills()
            ->withPivot('experience_years', 'experience_months')
            ->get() : [];
    }


    public function saveSkill()
    {

        $this->toast('success', 'Skill added successfully!');
        $this->loadUserSkills();
        $this->validate([
            'newSkill' => 'required|string',
            'experience_years' => 'required|numeric',
            'experience_months' => 'required|numeric'
        ]);

        $skill = userSkill::firstOrCreate(['name' => $this->newSkill]);
        $this->skill_id = $skill->id;
        // dd($this->editId,$this->user->skills());
        if ($this->editId) {
            $this->user->skills()->updateExistingPivot($this->editId, [
                'experience_years' => $this->experience_years,
                'experience_months' => $this->experience_months,
            ]);
        } else {
            $this->user->skills()->attach($this->skill_id, [
                'experience_years' => $this->experience_years,
                'experience_months' => $this->experience_months,
            ]);
        }


        $this->resetForm();

        $this->modalOpen = false;
    }

    public function toast($type, $message)
    {
        $this->dispatch('show-toast', type: $type, message: $message);
    }


    public function edit($skillId)
    {
        $this->resetValidation();
        $skill = $this->user->skills()->where('skill_id', $skillId)->first();

        $this->editId = $skillId;
        $this->newSkill = $skill->name;
        $this->experience_years = $skill->pivot->experience_years;
        $this->experience_months = $skill->pivot->experience_months;

        $this->loadUserSkills();
        $this->modalOpen = true;
    }

    public function delete($skillId)
    {
        $this->user->skills()->detach($skillId);
        $this->loadUserSkills();
    }

    public function resetForm()
    {
        $this->editId = null;
        $this->newSkill = '';
        $this->experience_years = '';
        $this->experience_months = '';
        $this->resetValidation();
        $this->loadUserSkills();

    }

    public function render()
    {
        return view('livewire.user.skill');
    }
}
