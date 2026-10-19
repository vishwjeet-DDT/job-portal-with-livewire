<div>
    @foreach ($fields as $name => $field)
        <div class="mb-3">
            {{-- Label (skip for checkbox to keep inline label) --}}
            @if($field['type'] !== 'checkbox')
                <label for="{{ $name }}" class="form-label">{{ $field['label'] }}</label>
            @endif

            {{-- Input Types --}}
            @switch($field['type'])
                @case('textarea')
                    <textarea id="{{ $name }}"
                              wire:model="formData.{{ $name }}"
                              class="form-control @error("formData.$name") is-invalid @enderror"></textarea>
                    @break

                @case('select')
                    <select id="{{ $name }}"
                            wire:model="formData.{{ $name }}"
                            class="form-select @error("formData.$name") is-invalid @enderror">
                        <option value="">-- Select --</option>
                        @foreach ($field['options'] ?? [] as $val => $label)
                            <option value="{{ $val }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    @break

                @case('checkbox')
                    <div class="form-check">
                        <input type="checkbox"
                               id="{{ $name }}"
                               wire:model="formData.{{ $name }}"
                               value="1"
                               class="form-check-input @error("formData.$name") is-invalid @enderror">
                        <label for="{{ $name }}" class="form-check-label">{{ $field['label'] }}</label>
                    </div>
                    @break

                @default
                    <input type="{{ $field['type'] ?? 'text' }}"
                           id="{{ $name }}"
                           wire:model="formData.{{ $name }}"
                           class="form-control @error("formData.$name") is-invalid @enderror">
            @endswitch

            {{-- Validation error --}}
            @error("formData.$name")
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>
    @endforeach
</div>
