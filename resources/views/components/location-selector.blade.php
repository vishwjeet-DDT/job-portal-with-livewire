@if($mobileNumber)
    <div class="col-md-{{$col}} mb-3">
        <!-- <x-input-label for="mobile_number" :value="__('Mobile Number')" />
                    <span class="text-danger">*</span></label>
                    <x-text-input id="mobile_number" class="form-control" type="text" name="mobile_number" :value="old('mobile_number')" required autofocus autocomplete="name" />
                    <x-input-error :messages="$errors->get('mobile_number')" class="mt-2" /> -->
        <label class="text-dark">Phone <span class="text-danger">*</span></label>
        <div class="input-group">
            <input id="country_code" name="emp_country_code" type="tel" class="form-control" style="max-width:75px;"
                required>
            <input type="number" placeholder="Phone Number" name="mobile_number" class="form-control typeNumber"
                value="{{ old('mobile_number') }}" required>
        </div>
    </div>
@endif
<div class="col-md-{{$col}}  mb-3">
    <label>Country</label>
    <span class="text-danger">*</span></label>
    <select class="form-control select2" id="country_id" name="country_id">
        <option value="">Select Country</option>
        @foreach($countries as $country)
            <option value="{{ $country->id }}" {{ $countryId == $country->id ? 'selected' : '' }}>
                {{ $country->name }}
            </option>
        @endforeach
    </select>
</div>

<div class="col-md-{{$col}} mb-3">
    <label>State</label>
    <span class="text-danger">*</span></label>
    <select class="form-control select2" id="state_id" name="state_id">
        <option value="">Select State</option>
    </select>
</div>

<div class="col-md-{{$col}} mb-3">
    <label>City</label>
    <span class="text-danger">*</span></label>
    <select class="form-control select2" id="city_id" name="city_id">
        <option value="">Select City</option>
    </select>
</div>

@push('js')
    <script src="https://cdn.ckeditor.com/ckeditor5/41.0.0/classic/ckeditor.js"></script>
    <script>
        $(document).ready(function () {
            $('.select2').select2();

            // Load states when a country is selected
            $('#country_id').change(function () {
                var countryId = $(this).val();
                $('#state_id').html('<option value="">Select State</option>');
                $('#city_id').html('<option value="">Select City</option>');

                if (countryId) {
                    $.get('/get-states/' + countryId, function (states) {
                        $.each(states, function (index, state) {
                            $('#state_id').append('<option value="' + state.id + '">' + state.name + '</option>');
                        });
                    });
                }
            });

            // Load cities when a state is selected
            $('#state_id').change(function () {
                var stateId = $(this).val();
                $('#city_id').html('<option value="">Select City</option>');

                if (stateId) {
                    $.get('/get-cities/' + stateId, function (cities) {
                        $.each(cities, function (index, city) {
                            $('#city_id').append('<option value="' + city.id + '">' + city.name + '</option>');
                        });
                    });
                }
            });

            // Preload states and cities if editing
            @if($countryId)
                $.get('/get-states/{{ $countryId }}', function (states) {
                    $.each(states, function (index, state) {
                        var selected = {{ $stateId ?? 'null' }} == state.id ? 'selected' : '';
                        $('#state_id').append('<option value="' + state.id + '" ' + selected + '>' + state.name + '</option>');
                    });

                    @if($stateId)
                        $.get('/get-cities/{{ $stateId }}', function (cities) {
                            $.each(cities, function (index, city) {
                                var selected = {{ $cityId ?? 'null' }} == city.id ? 'selected' : '';
                                $('#city_id').append('<option value="' + city.id + '" ' + selected + '>' + city.name + '</option>');
                            });
                        });
                    @endif
                        });
            @endif
        });

        $(document).ready(function () {
            const countryInput = document.getElementById('country_code');
            console.log('sdfds');
            const itiCountry = window.intlTelInput(countryInput, {
                separateDialCode: true,
                initialCountry: "in",
                preferredCountries: ["in", "us", "gb"]
            });

            countryInput.addEventListener('countrychange', function () {
                const countryData = itiCountry.getSelectedCountryData();
                countryInput.value = countryData.dialCode;
            });

            const initData = itiCountry.getSelectedCountryData();
            countryInput.value = initData.dialCode;
        });
    </script>
@endpush
