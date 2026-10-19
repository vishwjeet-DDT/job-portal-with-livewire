<?php

namespace App\Livewire\Role;

use Livewire\Component;
use App\Traits\HasDynamicForm;
use Spatie\Permission\Models\Role;

class Edit extends Component
{
    use HasDynamicForm;

    public $role;
    public $role_id;

    protected $listeners = ['setData'];

    public function mount($role = null, $role_id = null)
    {
        $this->role = $role;

        if ($role) {
            $this->loadPermission($role);
        }

        // Initialize form fields first
        $this->initializeFormFields(
            tableName: 'roles',
            excludeColumns: ['guard_name']
        );

        // Populate form fields with role data
        if ($this->role) {
            $this->formData = $this->role->toArray();
        }
    }

    public function setData($data)
    {
        $id = $data['id'] ?? null;

        if ($id) {
            $this->loadRole($id);
            $this->dispatch('modal-show', id: 'editModal');
        }
    }

    public function loadRole($id)
    {
        $role = Role::findOrFail($id);
        $this->role = $role;
        $this->formData = $role->toArray();
    }

    public function save()
    {
        // Ensure we have a role to update
        if (!$this->role) {
            session()->flash('error', 'No role selected for update.');
            return;
        }

        $this->validate([
            'formData.name' => 'required|string|max:255|unique:roles,name,' . $this->role->id,
            'formData.label' => 'nullable|string|max:255',
        ]);

        $this->role->update($this->formData);
        session()->flash('message', 'Role updated successfully!');
        $this->dispatch('modal-hide', id: 'editModal');
        $this->dispatch('refreshTable', id: 'dataTable');
    }

    public function render()
    {
        return view('livewire.role.edit');
    }
}
