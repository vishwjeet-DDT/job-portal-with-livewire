<?php

namespace App\Livewire\Industry;

use Livewire\Component;
use App\Traits\HasDynamicForm;
use App\Models\{Industry,IndustryType};

class Edit extends Component
{
     use HasDynamicForm;

    public $industry;
    public $industry_id;

    protected $listeners = ['setData'];

    public function mount($industry = null, $industry_id = null)
    {
        $this->role = $industry;

        if ($industry) {
            $this->loadPermission($industry);
        }

        // Initialize form fields first
        $this->initializeFormFields(
            tableName: 'industries',
            selectOptions: [
                'industry_types_id' => IndustryType::pluck('name', 'id')->toArray(),
            ]
        );

        if ($this->industry) {
            $this->formData = $this->industry->toArray();
        }
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
        $data = Industry::findOrFail($id);
        $this->industry = $data;
        $this->formData = $data->toArray();
    }

    public function save()
    {
        // Ensure we have a role to update
        if (!$this->industry) {
            session()->flash('error', 'No role selected for update.');
            return;
        }

        $this->validate([
            'formData.name' => 'required|string|max:255|unique:industries,name,' . $this->industry->id,
        ]);

        $this->industry->update($this->formData);
        session()->flash('message', 'Industry updated successfully!');
        $this->dispatch('modal-hide', id: 'editModal');
        $this->dispatch('refreshTable', id: 'dataTable');
    }

    public function render()
    {
        return view('livewire.industry.edit');
    }
}
