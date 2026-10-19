<div x-data="{
    selectedPermissions: @entangle('selectedPermissions'),
    init() {
        // Watch for changes from Livewire
        this.$watch('selectedPermissions', () => {
            this.$nextTick(() => {
                // Force reactivity update
                this.$el.querySelectorAll('input[type=checkbox]').forEach(input => {
                    input.dispatchEvent(new Event('change'));
                });
            });
        });
    }
}">

    <div class="form-group">
        <label>User Role</label>
        <select wire:model.live="selectedRole" class="form-control">
            <option value="">Select Role</option>
            @foreach($roles as $role)
                @if ($role->id == 3)
                    @continue
                @endif
                <option value="{{ $role->id }}">{{ $role->name }}</option>
            @endforeach
        </select>
    </div>

    @if($selectedRole && $permissions)
        <table class="table table-bordered mt-3">
            <thead>
                <tr>
                    <th>Permission</th>
                    <th>Allow</th>
                </tr>
            </thead>
            <tbody>
                @foreach($permissions as $parent)
                    <tr>
                        <td><b>{{ $parent->label ?? Str::title(Str::replace('.', ' ', $parent->name)) }}</b></td>
                        <td>
                            <input type="checkbox"
                                   x-model="selectedPermissions"
                                   value="{{ $parent->id }}"
                                   @change="
                                        let isChecked = $event.target.checked;
                                        let parentId = {{ $parent->id }};
                                        let childIds = [
                                            @foreach($parent->children as $child)
                                                {{ $child->id }},
                                            @endforeach
                                        ];

                                        if (isChecked) {
                                            // Add parent if not exists
                                            if (!selectedPermissions.includes(parentId)) {
                                                selectedPermissions.push(parentId);
                                            }
                                            // Add all children
                                            childIds.forEach(childId => {
                                                if (!selectedPermissions.includes(childId)) {
                                                    selectedPermissions.push(childId);
                                                }
                                            });
                                        } else {
                                            // Remove parent
                                            selectedPermissions = selectedPermissions.filter(id => id !== parentId);
                                            // Remove all children
                                            childIds.forEach(childId => {
                                                selectedPermissions = selectedPermissions.filter(id => id !== childId);
                                            });
                                        }
                                   ">
                        </td>
                    </tr>

                    @foreach($parent->children as $child)
                        <tr>
                            <td>&nbsp;&nbsp;&nbsp; ⇨ {{ Str::title(Str::replace('.', ' ', $child->name)) }}</td>
                            <td>
                                <input type="checkbox"
                                       x-model="selectedPermissions"
                                       value="{{ $child->id }}"
                                       @change="
                                            let isChecked = $event.target.checked;
                                            let childId = {{ $child->id }};
                                            let parentId = {{ $parent->id }};
                                            let siblingIds = [
                                                @foreach($parent->children as $sibling)
                                                    {{ $sibling->id }},
                                                @endforeach
                                            ];

                                            if (isChecked) {
                                                if (!selectedPermissions.includes(childId)) {
                                                    selectedPermissions.push(childId);
                                                }
                                                // Ensure parent is checked
                                                if (!selectedPermissions.includes(parentId)) {
                                                    selectedPermissions.push(parentId);
                                                }
                                            } else {
                                                selectedPermissions = selectedPermissions.filter(id => id !== childId);
                                                // Check if any siblings are still checked
                                                let hasCheckedSiblings = siblingIds.some(id => selectedPermissions.includes(id));
                                                if (!hasCheckedSiblings) {
                                                    selectedPermissions = selectedPermissions.filter(id => id !== parentId);
                                                }
                                            }
                                       ">
                            </td>
                        </tr>
                    @endforeach
                @endforeach
            </tbody>
        </table>

        <button wire:click="save" class="btn btn-primary">Save</button>
    @endif

    @if(session()->has('success'))
        <div class="alert alert-success mt-2">
            {{ session('success') }}
        </div>
    @endif
</div>
