{{-- resources/views/components/dynamic-form.blade.php --}}
@props(['fields' => [], 'formData' => 'formData', 'col' => 2,'multiSelect'=>0,'ignore'=>['languages','description','country_id','gender','employer_id','employment_type','work_mode','industry_type_id','shift','status']])

<div class="dynamic-form row row-cols-1 row-cols-md-{{ $col }}">
    @foreach ($fields as $name => $field)
    @php $is_ignore = (in_array($name, $ignore)); @endphp
    <div class="col mb-3 field-{{ $name }} @if ($name =='description' )
        col-md-12
    @endif" @if($is_ignore) wire:ignore @endif>
        {{-- Label (skip for checkbox to keep inline label) --}}
        @if($field['type'] !== 'checkbox')
        <label for="{{ $name }}" class="form-label text-dark">
            {{ $field['label'] }}
             @if ($field['required'])

            <span class="text-danger">*</span>
            @endif
        </label>
        @endif

        {{-- Input Types --}}
        @switch($field['type'])
        @case('textarea')
        <textarea id="{{ $name }}"
            wire:model="{{ $formData }}.{{ $name }}"
            class="form-control  @error(" {$formData}.{$name}") is-invalid @enderror"
            placeholder="{{ $field['placeholder'] ?? '' }}"
            rows="{{ $field['rows'] ?? 4 }}"
            @if($field['required'] ?? false) @endif></textarea>
        @break

     @case('select')
    @php
        $isMulti = ($multiSelect && in_array($name, ['city_id', 'languages','skill']));

        $selectedValues = old($formData.'.'.$name, data_get($this, $formData.'.'.$name));
        if ($selectedValues instanceof Illuminate\Support\Collection) {
            $selectedValues = $selectedValues->toArray();
        }
    @endphp

         @if ($name=='skill1')
             <livewire:skill-dropdown  :multiple="true" />
        @else
            <select id="{{ $name }}"
                data-model="{{ $formData }}.{{ $name }}"
                class="form-control select2 @error("{$formData}.{$name}") is-invalid @enderror"
                @if($isMulti) multiple @endif >

                <option value=""  @if($isMulti) disabled hidden @endif >-- Select {{ $field['label'] }} --</option>
                @if ($name=='skill')
                    <!-- <option value="add_new">+ Add New Skill</option> -->
                @endif

                @foreach ($field['options'] ?? [] as $val => $label)
                    <option value="{{ $val }}"
                        @if(is_array($selectedValues) && in_array($val, $selectedValues))
                            selected
                        @elseif($selectedValues == $val)
                            selected
                        @endif
                    >
                        {{ $label }}
                    </option>
                @endforeach
            </select>
         @endif
    @break


        @case('radio')
        <div class="radio-group">
            @foreach ($field['options'] ?? [] as $val => $label)
            <div class="form-check">
                <input type="radio"
                    id="{{ $name }}_{{ $val }}"
                    name="{{ $name }}"
                    wire:model="{{ $formData }}.{{ $name }}"
                    value="{{ $val }}"
                    class="form-check-input @error(" {$formData}.{$name}") is-invalid @enderror"
                    @if($field['required'] ?? false) @endif>
                <label for="{{ $name }}_{{ $val }}" class="form-check-label">{{ $label }}</label>
            </div>
            @endforeach
        </div>
        @break

        @case('checkbox')
        <div class="form-check">
            <input type="checkbox"
                id="{{ $name }}"
                wire:model="{{ $formData }}.{{ $name }}"
                value="1"
                class="form-check-input @error(" {$formData}.{$name}") is-invalid @enderror"
                @if($field['required'] ?? false) @endif>
            <label for="{{ $name }}" class="form-check-label">
                {{ $field['label'] }}
                @if($field['required'] ?? false)
                <span class="text-danger">*</span>
                @endif
            </label>
        </div>
        @break

        @case('file')
        <div class="file-upload-wrapper">
            <input type="file"
                id="{{ $name }}"
                wire:model="{{ $formData }}.{{ $name }}"
                class="form-control  @error(" {$formData}.{$name}") is-invalid @enderror"
                accept="{{ $field['accept'] ?? '' }}"
                max="{{ $field['max'] ?? 100 }}"
                @if($field['multiple'] ?? false) multiple @endif
                @if($field['required'] ?? false) @endif>
                <span id="fileError" class="text-danger"></span>

            {{-- Progress Bar --}}
            <div wire:loading wire:target="{{ $formData }}.{{ $name }}" class="mt-2">
                <div class="d-flex align-items-center">
                    <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                    <span class="text-muted">Uploading file...</span>
                </div>
                <div class="progress mt-2">
                    <div class="progress-bar progress-bar-striped progress-bar-animated"
                        role="progressbar"
                        style="width: 100%">
                    </div>
                </div>
            </div>

            {{-- File Preview --}}
            @if(in_array($name, ['profile_img', 'album_art_path', 'image']) && !empty($this->{$formData}[$name] ?? null))
            <div class="mt-2 p-2 bg-light rounded">

                {{-- If it's a new Livewire upload (object) --}}
                @if(is_object($this->{$formData}[$name]))
                @if($name === 'profile_img1' || $name === 'image')
                <div class="d-flex align-items-center">
                    <img src="{{ $this->{$formData}[$name]->temporaryUrl() }}"
                        alt="Preview"
                        class="me-2"
                        style="width: 50px; height: 50px; object-fit: cover;">
                    <div>
                        <small class="text-muted d-block">{{ $this->{$formData}[$name]->getClientOriginalName() }}</small>
                        <small class="text-muted">{{ number_format($this->{$formData}[$name]->getSize() / 1024, 1) }} KB</small>
                    </div>
                </div>
                @elseif($name === 'audio_path')
                <div class="d-flex align-items-center">
                    <i class="fas fa-music me-2 text-primary"></i>
                    <div>
                        <small class="text-muted d-block">{{ $this->{$formData}[$name]->getClientOriginalName() }}</small>
                        <small class="text-muted">{{ number_format($this->{$formData}[$name]->getSize() / 1024 / 1024, 1) }} MB</small>
                    </div>
                </div>
                @endif

                {{-- If it's an existing file path from DB (string) --}}
                @elseif(is_string($this->{$formData}[$name]))
                @if($name === 'profile_img' || $name === 'image')
                <img src="{{ asset('storage/' . $this->{$formData}[$name]) }}"
                    alt="Album Art"
                    style="width: 100px; height: 100px; object-fit: cover;">
                @elseif($name === 'audio_path')
                <audio controls style="width: 100%">
                    <source src="{{ asset('storage/' . $this->{$formData}[$name]) }}" type="audio/mpeg">
                    Your browser does not support the audio element.
                </audio>
                @endif
                @endif
            </div>
            @endif
        </div>

        @if($field['help'] ?? false)
        <div class="form-text">{{ $field['help'] }}</div>
        @endif
        @break


        @case('range')
        <input type="range"
            id="{{ $name }}"
            wire:model="{{ $formData }}.{{ $name }}"
            class="form-range @error(" {$formData}.{$name}") is-invalid @enderror"
            min="{{ $field['min'] ?? 0 }}"
            max="{{ $field['max'] ?? 100 }}"
            step="{{ $field['step'] ?? 1 }}"
            @if($field['required'] ?? false) @endif>
        <div class="d-flex justify-content-between">
            <small>{{ $field['min'] ?? 0 }}</small>
            <small>{{ $field['max'] ?? 100 }}</small>
        </div>
        @break

        @default
        <input type="{{ $field['type'] ?? 'text' }}"
            id="{{ $name }}"
            wire:model="{{ $formData }}.{{ $name }}"
            class="form-control @error(" {$formData}.{$name}") is-invalid @enderror"
            placeholder="{{ $field['placeholder'] ?? '' }}"
            @if($field['min'] ?? false) min="{{ $field['min'] }}" @endif
            @if($field['max'] ?? false) max="{{ $field['max'] }}" @endif
            @if($field['step'] ?? false) step="{{ $field['step'] }}" @endif
            @if($field['pattern'] ?? false) pattern="{{ $field['pattern'] }}" @endif
            @if($field['required'] ?? false) @endif>
        @endswitch

        {{-- Validation error --}}
        @error("{$formData}.{$name}")
        <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror

        {{-- Help text --}}
        @if(($field['help'] ?? false) && $field['type'] !== 'file')
        <div class="form-text">{{ $field['help'] }}</div>
        @endif
    </div>
    @endforeach

    <script>
        document.getElementById('audio_path').addEventListener('change', function() {
            const file = this.files[0];
            const maxSize = 100 * 1024 * 1024; // 100 MB in bytes
            const errorSpan = document.getElementById('fileError');

            if (file && file.size > maxSize) {
                errorSpan.textContent = "File is too large. Maximum allowed size is 100MB.";
                this.value = ""; // clear the file input
            } else {
                errorSpan.textContent = "";
            }
        });
    </script>
</div>
