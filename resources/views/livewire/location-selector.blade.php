<div class="row">
    @if($mobileNumber)
    <div class="col-md-{{ $col }} mb-3">
        <label class="text-dark">Phone <span class="text-danger">*</span></label>
        <div class="input-group" wire:ignore>
            <input id="country_code" name="emp_country_code" type="tel"
                class="form-control" style="max-width:75px;" required>
            <input type="number" placeholder="Phone Number" wire:model.defer="mobileNumber"
                name="mobile_number" class="form-control typeNumber" required>
        </div>
    </div>
    @endif

    <div class="col-md-{{ $col }} mb-3">
        <label>Country <span class="text-danger">*</span></label>
        <select wire:model.live="countryId" class="form-control" id="country_{{ $componentId }}">
            <option value="">Select Country</option>
            @foreach($countries as $country)
                <option value="{{ $country->id }}">{{ $country->name }}</option>
            @endforeach
        </select>
    </div>

    <div class="col-md-{{ $col }} mb-3">
        <label>State <span class="text-danger">*</span></label>
        <select wire:model.live="stateId" class="form-control" id="state_{{ $componentId }}">
            <option value="">Select State</option>
            @foreach($states as $state)
                <option value="{{ $state->id }}">{{ $state->name }}</option>
            @endforeach
        </select>
    </div>

    <div class="col-md-{{ $col }} mb-3">
        <label>City <span class="text-danger">*</span></label>
        <select wire:model.live="cityId" class="form-control" id="city_{{ $componentId }}">
            <option value="">Select City</option>
            @foreach($cities as $city)
                <option value="{{ $city->id }}">{{ $city->name }}</option>
            @endforeach
        </select>
    </div>
</div>

@push('js')
<script src="https://cdn.ckeditor.com/ckeditor5/41.0.0/classic/ckeditor.js"></script>
<script>
let itiCountry_{{ $componentId }} = null;

function initCountryTelInput_{{ $componentId }}(countryCode = "in") {
    const countryInput = document.getElementById('country_code');
    if (!countryInput) {
        console.warn('country_code input not found');
        return;
    }

    // Destroy previous instance if exists
    if (itiCountry_{{ $componentId }}) {
        itiCountry_{{ $componentId }}.destroy();
        itiCountry_{{ $componentId }} = null;
    }

    // Initialize intlTelInput
    itiCountry_{{ $componentId }} = window.intlTelInput(countryInput, {
        separateDialCode: true,
        initialCountry: countryCode.toLowerCase() || "in",
        preferredCountries: ["in", "us", "gb"]
    });
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function () {
    initCountryTelInput_{{ $componentId }}();
});

// Reinitialize after Livewire updates (for Livewire v2)
document.addEventListener('livewire:load', function () {
    Livewire.hook('message.processed', (message, component) => {
        if (component.id === '{{ $componentId }}') {
            // Small delay to ensure DOM is updated
            setTimeout(() => {
                initCountryTelInput_{{ $componentId }}();
            }, 100);
        }
    });
});

// For Livewire v3
document.addEventListener('livewire:initialized', () => {
    Livewire.hook('morph.updated', ({ component }) => {
        if (component.id === '{{ $componentId }}') {
            setTimeout(() => {
                initCountryTelInput_{{ $componentId }}();
            }, 100);
        }
    });
});
</script>
@endpush
