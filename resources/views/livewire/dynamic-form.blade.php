<div>
    @if ($successMessage)
        <div class="alert alert-success">{{ $successMessage }}</div>
    @endif

    <form wire:submit.prevent="submit">
        @foreach ($fields as $name => $field)
            <div class="mb-3">
                <label for="{{ $name }}" class="form-label">{{ $field['label'] }}</label>
                @if ($field['type'] === 'textarea')
                    <textarea wire:model="formData.{{ $name }}"
                              id="{{ $name }}"
                              class="form-control"></textarea>
                @elseif ($field['type'] === 'select')
                    <select wire:model="formData.{{ $name }}"
                            id="{{ $name }}"
                            class="form-select">
                        <option value="">-- Select --</option>
                        @foreach ($field['options'] as $val => $label)
                            <option value="{{ $val }}">{{ $label }}</option>
                        @endforeach
                    </select>

                {{-- ✅ Checkbox --}}
                @elseif ($field['type'] === 'checkbox')
                    <div class="form-check">
                        <input type="checkbox" wire:model="formData.{{ $name }}"
                               id="{{ $name }}"
                               class="form-check-input">
                        <label class="form-check-label" for="{{ $name }}">{{ $field['label'] }}</label>
                    </div>
                @elseif ($field['type'] === 'password')
                    <div class="input-group">
                        <input :type="$wire.passwordVisible ? 'text' : 'password'"
                               id="{{ $name }}"
                               class="form-control">
                        <button type="button"
                                class="btn btn-outline-secondary"
                                wire:click="$toggle('passwordVisible')">
                            {{ $passwordVisible ? 'Hide' : 'Show' }}
                        </button>
                    </div>
                @else
                    <input type="{{ $field['type'] }}"
                           wire:model="formData.{{ $name }}"
                           id="{{ $name }}"
                           class="form-control">
                @endif

                @error("formData.$name")
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
        @endforeach

        <button type="submit" class="btn btn-primary">
            {{ $record ? 'Update' : 'Create' }}
        </button>
    </form>
</div>
