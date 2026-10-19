<?php

namespace App\Livewire\IndustryType;

use Livewire\Component;
use App\Traits\HasDynamicForm;
use App\Models\IndustryType;

class Edit extends Component
{
      use HasDynamicForm;

    public $industry_type;
    public $industry_type_id;

    protected $listeners = ['setData'];

    public function mount($industry_type = null, $industry_type_id = null)
    {
        $this->industry_type = $industry_type;

        if ($industry_type) {
            $this->loadPermission($industry_type);
        }

        // Initialize form fields first
        $this->initializeFormFields(
            tableName: 'industry_types',
        );

        if ($this->industry_type) {
            $this->formData = $this->industry_type->toArray();
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
        $data = IndustryType::findOrFail($id);
        $this->industry_type = $data;
        $this->formData = $data->toArray();
    }

    public function save()
    {
        // Ensure we have a role to update
        if (!$this->industry_type) {
            session()->flash('error', 'No Industry Type selected for update.');
            return;
        }

        $this->validate([
            'formData.name' => 'required|string|max:255|unique:industry_types,name,' . $this->industry->id,
        ]);

        $this->industry->update($this->formData);
        session()->flash('message', 'Industry Type updated successfully!');
        $this->dispatch('modal-hide', id: 'editModal');
        $this->dispatch('refreshTable', id: 'dataTable');
    }

    public function render()
    {
        return view('livewire.industry-type.edit');
    }
}
