<div>
    <form wire:submit.prevent="update" enctype="multipart/form-data">
        <div class="card borderRadius">
            <div class="card-body p-3">
                <div class="row formselector">
                    {{-- Employer Name --}}
                    <div class="col-md-4 mt-3">
                        <label>Employer Name <span class="text-danger">*</span></label>
                        <input type="text" wire:model="user.name" class="form-control">
                        @error('user.name') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>

                    {{-- Email --}}
                    <div class="col-md-4 mt-3">
                        <label>Email <span class="text-danger">*</span></label>
                        <input type="email" wire:model="user.email" class="form-control">
                        @error('user.email') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>

                    {{-- Phone --}}
                    <div class="col-md-4 mt-3">
                        <label class="text-dark">Phone <span class="text-danger">*</span></label>
                        <div class="input-group" wire:ignore>
                            <input id="country_code" type="tel" class="form-control" style="max-width:75px;">
                            <input type="number" placeholder="Phone Number" wire:model="user.mobile_number" class="form-control typeNumber">
                        </div>
                        @error('user.mobile_number') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>

                    {{-- CEO Name --}}
                    <div class="col-md-4 mt-3">
                        <label>CEO Name</label>
                        <input type="text" wire:model="ceo_name" class="form-control">
                        @error('ceo_name') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>

                    {{-- Company Name --}}
                    <div class="col-md-4 mt-3">
                        <label>Company Name <span class="text-danger">*</span></label>
                        <input type="text" wire:model="company_name" class="form-control">
                        @error('company_name') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>

                    {{-- Logo --}}
                    <div class="col-md-4 mt-3" >
                        <label>Logo</label>
                        <input type="file" wire:model="logo" class="form-control" id="compny_logo">
                            <img src="" width="100" class="mt-2" id="priview_image" wire:ignore>
                        @if ($logo)
                        @elseif ($old_logo)
                            <img src="{{ Storage::url($old_logo) }}" width="100" class="mt-2">
                        @endif
                        @error('logo') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>

                    {{-- Industry --}}
                    <div class="col-md-4 mt-3" wire:ignore>
                        <label>Industry <span class="text-danger">*</span></label>
                        <select data-model="industry_id" class="form-control select2">
                            <option value="">Select Industry</option>
                            @foreach ($industries as $ind)
                                <option value="{{ $ind->id }}" @selected($industry_id == $ind->id)>{{ $ind->name }}</option>
                            @endforeach
                        </select>
                        @error('industry_id') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>

                    {{-- Functional Area --}}
                    <div class="col-md-4 mt-3">
                        <label>Functional Area <span class="text-danger">*</span></label>
                        <select data-model="functional_area_id" class="form-control select2">
                            <option value="">Select Functional Area</option>
                            @foreach ($functionalAreas as $fa)
                                <option value="{{ $fa->id }}" @selected($functional_area_id == $fa->id)>{{ $fa->name }}</option>
                            @endforeach
                        </select>
                        @error('functional_area_id') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>

                    {{-- Ownership Type --}}
                    <div class="col-md-4 mt-3" wire:ignore>
                        <label>Ownership Type <span class="text-danger">*</span></label>
                        <select data-model="ownership_type" class="form-control select2">
                            <option value="">Select Ownership Type</option>
                            <option value="Sole Proprietorship" @selected($ownership_type == 'Sole Proprietorship')>Sole Proprietorship</option>
                            <option value="Partnership" @selected($ownership_type == 'Partnership')>Partnership</option>
                            <option value="Limited Liability Company" @selected($ownership_type == 'Limited Liability Company')>Limited Liability Company</option>
                            <option value="Cooperative" @selected($ownership_type == 'Cooperative')>Cooperative</option>
                            <option value="Joint Venture" @selected($ownership_type == 'Joint Venture')>Joint Venture</option>
                            <option value="Private Limited" @selected($ownership_type == 'Private Limited')>Private Limited</option>
                            <option value="Limited" @selected($ownership_type == 'Limited')>Limited</option>
                        </select>
                        @error('ownership_type') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>

                    {{-- Country --}}
                    <div class="col-md-4 mt-3" wire:ignore>
                        <label>Country <span class="text-danger">*</span></label>
                        <select data-model="user.country_id" class="form-control select2">
                            <option value="">Select Country</option>
                            @foreach(App\Models\Country::all() as $country)
                                <option value="{{ $country->id }}" @selected($user['country_id'] == $country->id)>{{ $country->name }}</option>
                            @endforeach
                        </select>
                        @error('user.country_id') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>

                    {{-- State --}}
                    <div class="col-md-4 mt-3">
                        <label>State <span class="text-danger">*</span></label>
                        <select data-model="user.state_id" class="form-control select2">
                            <option value="">Select State</option>
                            @foreach($states as $state)
                                <option value="{{ $state->id }}" @selected($user['state_id'] == $state->id)>{{ $state->name }}</option>
                            @endforeach
                        </select>
                        @error('user.state_id') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>

                    {{-- City --}}
                    <div class="col-md-4 mt-3" >
                        <label>City <span class="text-danger">*</span></label>
                        <select data-model="user.city_id" class="form-control select2">
                            <option value="">Select City</option>
                            @foreach($cities as $city)
                                <option value="{{ $city->id }}" @selected($user['city_id'] == $city->id)>{{ $city->name }}</option>
                            @endforeach
                        </select>
                        @error('user.city_id') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>

                    {{-- Employee Strength --}}
                    <div class="col-md-4 mt-3">
                        <label>Employee Strength <span class="text-danger">*</span></label>
                        <input type="text" wire:model="company_size" class="form-control">
                        @error('company_size') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>

                    {{-- Established Year --}}
                    <div class="col-md-4 mt-3" wire:ignore>
                        <label>Established Year <span class="text-danger">*</span></label>
                        <select data-model="established_year" class="form-control select2">
                            <option value="">Select Year</option>
                            @for ($year = date('Y'); $year >= 1900; $year--)
                                <option value="{{ $year }}" @selected($established_year == $year)>{{ $year }}</option>
                            @endfor
                        </select>
                        @error('established_year') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>

                    {{-- Location --}}
                    <div class="col-md-4 mt-3">
                        <label>Location <span class="text-danger">*</span></label>
                        <input type="text" wire:model="location" class="form-control">
                        @error('location') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>

                    {{-- Second Office Location --}}
                    <div class="col-md-4 mt-3">
                        <label>2nd Office Location</label>
                        <input type="text" wire:model="second_office_location" class="form-control">
                    </div>

                    {{-- Description --}}
                    <div class="col-md-12 mt-3" wire:ignore>
                        <label>About Employer <span class="text-danger">*</span></label>
                        <textarea id="editor" class="form-control">{!! $description !!}</textarea>
                        @error('description') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>

                    {{-- Website --}}
                    <div class="col-md-6 mt-3">
                        <label>Website <span class="text-danger">*</span></label>
                        <input type="text" wire:model="website" class="form-control">
                        @error('website') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>

                    {{-- Facebook --}}
                    <div class="col-md-6 mt-3">
                        <label>Facebook URL</label>
                        <input type="text" wire:model="facebook_url" class="form-control">
                        @error('facebook_url') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>

                    {{-- LinkedIn --}}
                    <div class="col-md-6 mt-3">
                        <label>LinkedIn URL</label>
                        <input type="text" wire:model="linkedin_url" class="form-control">
                        @error('linkedin_url') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                </div>

                {{-- Submit --}}
                <div class="submitBtn mt-4 text-end">
                    <button type="submit" class="btn btnBg text-white">Update</button>
                </div>
            </div>
        </div>
    </form>

@push('js')
<script src="https://cdn.ckeditor.com/ckeditor5/41.0.0/classic/ckeditor.js"></script>
<script>
let editorInstance;

$(document).ready(function () {
    $('.select2').select2();
});

document.addEventListener('DOMContentLoaded', function () {
    let typingTimer;
    const doneTypingDelay = 1500;

    ClassicEditor.create(document.querySelector('#editor')).then(editor => {
        editorInstance = editor;

        editor.model.document.on('change:data', () => {
            clearTimeout(typingTimer);
            @this.set('description', editor.getData());
            typingTimer = setTimeout(() => $('.select2').select2(), doneTypingDelay);
        });
    }).catch(error => console.error(error));

    $('.select2').on('change', function (e) {
        const model = $(this).data('model');
        const value = $(this).val();
        console.log(value);
        console.log(model);
        if (model) {
            @this.set(model, value);
            setTimeout(() => $('.select2').select2(), 500);
        }
    });

    const countryInput = document.getElementById('country_code');
    window.intlTelInput(countryInput, {
        separateDialCode: true,
        initialCountry: "in",
        preferredCountries: ["in", "us", "gb"]
    });
});

window.addEventListener('refresh-select2', () => {
    setTimeout(() => $('.select2').select2(), 200);
});


 document.getElementById('compny_logo').addEventListener('change', function (event) {
        let file =  event.target.files[0];
            if(file){
                const imageUrl = URL.createObjectURL(file);
                document.getElementById('priview_image').src = imageUrl;
            };
 });
</script>
@endpush
</div>
