@props(['options' => [], 'placeholder' => 'Select options...'])

@once
    @push('css')
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />
    @endpush
    @push('js')
        <script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
    @endpush
@endonce

<div wire:ignore x-data="{
        instance: null,
        init() {
            this.instance = new Choices(this.$refs.select, {
                removeItemButton: true,
                placeholder: true,
                placeholderValue: '{{ $placeholder }}',
                shouldSort: false,
            });

            // Sync changes back to Livewire
            this.instance.passedElement.element.addEventListener('change', () => {
                $wire.$set(
                    '{{ $attributes->whereStartsWith('wire:model')->first() }}',
                    this.instance.getValue(true),
                    false  // set true to trigger backend request on every change
                );
            });
        }
    }">
    <select x-ref="select" multiple {{ $attributes->except('wire:model') }}>
        @foreach($options as $value => $label)
            <option value="{{ $value }}">{{ $label }}</option>
        @endforeach
    </select>
</div>
