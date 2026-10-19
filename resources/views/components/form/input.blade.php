@props([
    'label' => null,
    'name',
    'type' => 'text',
    'placeholder' => '',
    'required' => false,
    'col' => 6,
    'model' => null,  {{-- for Livewire --}}
])

<div class="col-md-{{ $col }} mb-3">
    <label for="{{ $name }}" class="form-label text-dark">
        {{ $label }}
        @if($required)
            <span class="text-danger">*</span>
        @endif
    </label>

    <input
        id="{{ $name }}"
        type="{{ $type }}"
        placeholder="{{ $placeholder }}"
        {{ $required ? 'required' : '' }}
        class="form-control @error($name) is-invalid @enderror"
        @if($model)
            wire:model.defer="{{ $model }}"
        @else
            name="{{ $name }}"
        @endif
    >

    @error($name)
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>
