<?php

namespace App\Livewire\Skill;

use App\Models\Skill;
use Livewire\Component;
use App\Traits\HasDynamicForm;

class Edit extends Component
{
    use HasDynamicForm;

    public $skill;
    public $skill_id;

    protected $listeners = ['setData'];

   public function mount()
    {
        $this->initializeFormFields(
            tableName: 'skills'
        );
    }

    public function setData($data)
    {
        $id = $data['id'] ?? null;

        if ($id) {
            $this->loadData($id);
            $this->dispatch('modal-show', id: 'editModal');
        }
    }

    public function loadData($id)
    {
        $data = Skill::findOrFail($id);
        $this->skill = $data;
        $this->formData = $data->toArray();
    }

    public function save()
    {
        // Ensure we have a role to update
        if (!$this->skill) {
            session()->flash('error', 'No Skill selected for update.');
            return;
        }

        $this->validate([
            'formData.name' => 'required|string|max:255|unique:skills,name,' . $this->skill->id,
        ]);

        $this->skill->update($this->formData);
        session()->flash('message', 'Skill updated successfully!');
        $this->dispatch('modal-hide', id: 'editModal');
        $this->dispatch('refreshTable', id: 'dataTable');
    }
    public function render()
    {
        return view('livewire.skill.edit');
    }
}
