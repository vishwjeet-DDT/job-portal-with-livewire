<?php

namespace App\Livewire\Permission;

use Livewire\Component;
use App\Traits\HasDynamicForm;
use App\Models\Permission;
class Create extends Component
{
    use HasDynamicForm;
    public function mount()
    {
        $this->initializeFormFields(
            tableName: 'permissions',
            excludeColumns: ['guard_name'],
            selectOptions: [
                'parent_id' => Permission::where('parent_id', null)->get()->pluck('name', 'id')->toArray()
            ]
        );
    }

    public function save()
    {
        $this->validate([
            'formData.name' => 'required|string|max:255|unique:permissions,name',
            'formData.label' => 'nullable|string|max:255',
            'formData.parent_id' => 'nullable|exists:permissions,id',
        ]);

        Permission::create($this->formData);
        session()->flash('message', 'Permission created successfully!');
        $this->reset('formData');
        $this->dispatch('modal-hide', id: 'createModal');
        $this->dispatch('refreshTable', id: 'dataTable');
    }
    public function render()
    {
        return view('livewire.permission.create');
    }
}
