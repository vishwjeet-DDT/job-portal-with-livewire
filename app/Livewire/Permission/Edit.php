<?php
namespace App\Livewire\Permission;

use Livewire\Component;
use App\Models\Permission;
use App\Traits\HasDynamicForm;

class Edit extends Component
{
    use HasDynamicForm;

    public $permissionId;
    public $permissions;
    // public $formData = [];

    protected $listeners = ['setData'];

    public function mount($permissions = null)
    {
        if ($permissions) {
            $this->loadPermission($permissions);
        }

        $this->initializeFormFields(
            tableName: 'permissions',
            excludeColumns: ['guard_name'],
            selectOptions: [
                'parent_id' => Permission::where('parent_id', null)->get()->pluck('name', 'id')->toArray()
            ]

        );

        if ($this->permissions) {
            $this->formData = $this->permissions->toArray();
            $this->populateFormData($this->permissions);
        }
    }

    public function setData($data)
    {
        $id = $data['id'] ?? null;

        if ($id) {
            $this->loadPermission($id);
            $this->dispatch('modal-show', id: 'editModal');
        }
    }

    public function loadPermission($id)
    {
        $permission = Permission::findOrFail($id);
        $this->permissions = $permission;
        $this->permissionId = $permission->id;
        $this->formData = $permission->toArray();
    }

    public function save()
    {
        $permission = Permission::findOrFail($this->permissionId);
        $permission->update($this->formData);

        session()->flash('message', 'Permission created successfully!');
        $this->dispatch('modal-hide', id: 'editModal');
        $this->dispatch('refreshTable', id: 'dataTable');

    }

    public function render()
    {
        return view('livewire.permission.edit');
    }
}
