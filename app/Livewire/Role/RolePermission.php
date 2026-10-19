<?php

namespace App\Livewire\Role;

use Livewire\Component;
use App\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermission extends Component
{
    public $roles = [];
    public $permissions = [];
    public $selectedRole = null;
    public $selectedPermissions = [];

    public function mount()
    {
        $this->roles = Role::all();
    }

    public function updatedSelectedRole($roleId)
    {
        $this->permissions = Permission::with('children')->whereNull('parent_id')->get();
        // dd($this->permissions->toArray());
        $role = Role::with('permissions')->find($roleId);


        if ($role) {
            $this->selectedPermissions = $role->permissions->pluck('id')->toArray();
        } else {
            $this->selectedPermissions = [];
        }

        // Force re-render to update Alpine.js state
        $this->dispatch('permissions-updated');
    }

    public function toggleParentPermission($parentId, $checked)
    {
        $parent = Permission::with('children')->find($parentId);

        if ($checked) {
            // Add parent
            if (!in_array($parentId, $this->selectedPermissions)) {
                $this->selectedPermissions[] = $parentId;
            }

            // Add all children
            foreach ($parent->children as $child) {
                if (!in_array($child->id, $this->selectedPermissions)) {
                    $this->selectedPermissions[] = $child->id;
                }
            }
        } else {
            // Remove parent
            $this->selectedPermissions = array_values(array_filter($this->selectedPermissions, fn($id) => $id !== $parentId));

            // Remove all children
            foreach ($parent->children as $child) {
                $this->selectedPermissions = array_values(array_filter($this->selectedPermissions, fn($id) => $id !== $child->id));
            }
        }
    }

    public function toggleChildPermission($childId, $parentId, $checked)
    {
        $parent = Permission::with('children')->find($parentId);

        if ($checked) {
            // Add child
            if (!in_array($childId, $this->selectedPermissions)) {
                $this->selectedPermissions[] = $childId;
            }

            // Ensure parent is checked if any child is checked
            if (!in_array($parentId, $this->selectedPermissions)) {
                $this->selectedPermissions[] = $parentId;
            }
        } else {
            // Remove child
            $this->selectedPermissions = array_values(array_filter($this->selectedPermissions, fn($id) => $id !== $childId));

            // Check if all siblings are unchecked, then uncheck parent
            $siblings = $parent->children->pluck('id')->toArray();
            $hasChecked = array_intersect($siblings, $this->selectedPermissions);

            if (empty($hasChecked)) {
                $this->selectedPermissions = array_values(array_filter($this->selectedPermissions, fn($id) => $id !== $parentId));
            }
        }
    }

    public function save()
    {
        $role = Role::find($this->selectedRole);

        if ($role) {
            $role->permissions()->sync($this->selectedPermissions);
            session()->flash('success', 'Permissions updated successfully.');
        }
    }

    public function render()
    {
        return view('livewire.role.role-permission');
    }
}
