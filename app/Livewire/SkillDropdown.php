<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Skill;

class SkillDropdown extends Component
{
    public $skills = [];
    public $selectedSkill = ''; // Add this property
    public $newSkill = '';
    public $col=4;
    public $multiple = false;

    protected $listeners = ['close-skill-modal' => 'loadSkills']; // Fixed event name

    public function mount($selectedSkill = null, $multiple = false)
    {
        $this->multiple = $multiple;

        // Default: if single mode -> string, if multi -> array
        if ($multiple) {
            $this->selectedSkill = is_array($selectedSkill) ? $selectedSkill : [];
        } else {
            $this->selectedSkill = $selectedSkill ?? '';
        }
        $this->loadSkills();
    }

    public function loadSkills()
    {
        $this->skills = Skill::orderBy('name')->get();
    }

    public function updatedSelectedSkill($value)
    {

        // MULTIPLE MODE
        if ($this->multiple) {
            if (is_array($this->selectedSkill) && in_array("add_new", $this->selectedSkill)) {
                $this->selectedSkill = array_filter($this->selectedSkill, function ($item) {
                    return $item !== "add_new";
                });

                $this->dispatch('open-skill-modal');
            }
            return;
        }
        if ($value === "add_new") {
            $this->dispatch('open-skill-modal');
        }
    }


    public function saveSkill()
    {
        if ($this->newSkill) {
            $skill = Skill::firstOrCreate(['name' => $this->newSkill]);
            $this->newSkill = '';

            if ($this->multiple) {
                    $this->selectedSkill = array_filter($this->selectedSkill, function ($item) {
                        return $item !== "add_new";
                    });

                    // dd($this->selectedSkill);
                $this->selectedSkill[] = $skill->id;
                $this->selectedSkill = array_unique($this->selectedSkill);
            } else {
                $this->selectedSkill = $skill->id;
            }


            $this->loadSkills();
        }
        if ($this->multiple) {
            $this->dispatch('close-skill-modal');
        }else{
            $this->dispatch('close-skill-modal2');
        }
    }


    public function render()
    {
        return view('livewire.skill-dropdown');
    }
}
