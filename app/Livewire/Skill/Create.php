<?php

namespace App\Livewire\Skill;

use Livewire\Component;
use App\Traits\HasDynamicForm;
use App\Models\Skill;

class Create extends Component
{

    use HasDynamicForm;
    public function mount()
    {
        $this->initializeFormFields('skills');
    }

    public function save()
    {
        $this->validate([
            'formData.name' => 'required|string|max:255|unique:skills,name',
        ]);
        Skill::create($this->formData);
        session()->flash('message', 'Skill created successfully!');
        $this->reset('formData');
        $this->dispatch('modal-hide', id: 'createModal');
        $this->dispatch('refreshTable', id: 'dataTable');
    }
    public function render()
    {
        return view('livewire.skill.create');
    }
}
