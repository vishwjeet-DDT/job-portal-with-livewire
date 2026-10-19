<?php

namespace App\Livewire\Role;

use Livewire\Component;
use App\Traits\HasDynamicForm;
use Spatie\Permission\Models\Role;

class Create extends Component
{
    use HasDynamicForm;

    public function mount()
    {
        $this->initializeFormFields(
            tableName: 'roles',
            excludeColumns: ['guard_name']
        );
    }

    public function save()
    {
        $this->validate([
            'formData.name' => 'required|string|max:255|unique:roles,name',
            'formData.label' => 'nullable|string|max:255',
        ]);

        $this->formData['guard_name'] = 'web';
        Role::create($this->formData);
        session()->flash('message', 'Role created successfully!');
        $this->reset('formData');
        $this->dispatch('modal-hide', id: 'createModal');
        $this->dispatch('refreshTable', id: 'dataTable');
    }
    public function render()
    {
        return view('livewire.role.create');
    }
}
