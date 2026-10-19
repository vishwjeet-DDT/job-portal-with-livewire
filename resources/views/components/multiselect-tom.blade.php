@props([
    'options' => [],
    'placeholder' => 'Select...',
    'valueField' => 'id',
    'labelField' => 'name',
    'multiselect' => false,
    'delay' => 1000,
])

@once
    @push('css')
        <link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.min.css" rel="stylesheet" />
    @endpush
    @push('js')
        <script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
    @endpush
@endonce

@php
    $normalized = collect($options)->mapWithKeys(function ($item) use ($valueField, $labelField) {
        if (is_object($item))
            return [(string) $item->{$valueField} => $item->{$labelField}];
        if (is_array($item))
            return [(string) $item[$valueField] => $item[$labelField]];
        return [(string) $item => $item];
    })->toArray();

    $isMulti = filter_var($multiselect, FILTER_VALIDATE_BOOLEAN);
    $wireModel = $attributes->whereStartsWith('wire:model')->first();
    $optionsJson = json_encode($normalized);
@endphp

<div x-data="{
        instance: null,
        isMulti: {{ $isMulti ? 'true' : 'false' }},
        options: {{ $optionsJson }},
        timer: null,

        initTomSelect() {
            // Clear any pending init
            if (this.timer) {
                clearTimeout(this.timer);
            }

            this.timer = setTimeout(() => {
                // Destroy old instance if exists
                if (this.instance) {
                    this.instance.destroy();
                    this.instance = null;
                }

                // Rebuild <option> elements
                const select = this.$refs.select;
                select.innerHTML = '<option value=\'\'></option>';
                Object.entries(this.options).forEach(([value, label]) => {
                    const opt = document.createElement('option');
                    opt.value = value;
                    opt.textContent = label;
                    select.appendChild(opt);
                });

                // Init Tom Select
                this.instance = new TomSelect(select, {
                    plugins: this.isMulti ? ['remove_button'] : [],
                    placeholder: '{{ $placeholder }}',
                    persist: false,
                    create: false,
                });

                // Sync to Livewire on change
                this.instance.on('change', (value) => {
                    if (this.isMulti) {
                        $wire.$set('{{ $wireModel }}', value ? value.split(',') : [], false);
                    } else {
                        $wire.$set('{{ $wireModel }}', value, true);
                    }
                });

            }, {{ $delay }});
        }
    }" x-init="initTomSelect()" x-effect="options = {{ $optionsJson }}; initTomSelect()">
    <select x-ref="select" {{ $isMulti ? 'multiple' : '' }} {{ $attributes->except('wire:model') }}>
        <option value="">{{ $placeholder }}</option>
        @foreach($normalized as $value => $label)
            <option value="{{ $value }}">{{ $label }}</option>
        @endforeach
    </select>
</div>
