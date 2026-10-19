<x-guest-layout>
    @section('guest_heading', "Register")
    <form method="POST" action="{{ route('register') }}" enctype="multipart/form-data">
        @csrf
        <div class="row">
            <div class="col-md-6 mb-3">
                <x-input-label for="first_name" :value="__('First Name')" />
                <span class="text-danger">*</span></label>
                <x-text-input id="first_name" class="form-control" type="text" name="first_name"
                    :value="old('first_name')" required autofocus autocomplete="name" />

                @error('first_name')
                    <div class="text-danger text-sm">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-md-6 mb-3">
                <x-input-label for="last_name" :value="__('Last Name')" />
                <span class="text-danger">*</span></label>
                <x-text-input id="last_name" class="form-control" type="text" name="last_name" :value="old('last_name')"
                    required autofocus autocomplete="last_name" />

                @error('last_name')
                    <div class="text-danger text-sm">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-md-6 mb-3">
                <x-input-label :value="__('Resume')" />
                <span class="text-danger">*</span></label>
                <x-text-input id="last_name" class="form-control" type="file" name="resume" :value="old('resume')"
                    required autofocus autocomplete="resume" />

                @error('resume')
                    <div class="text-danger text-sm">{{ $message }}</div>
                @enderror
            </div>
              <!-- Email Address -->
        <div class=" col-md-6 mb-3">
            <x-input-label for="email" :value="__('Email')" />
            <span class="text-danger">*</span></label>
            <x-text-input id="email" class="block mt-1 w-full form-control" type="email" name="email"
                :value="old('email')" required autocomplete="username" />

            @error('email')
                <div class="text-danger text-sm">{{ $message }}</div>
            @enderror
        </div>
        </div>


        <div class="row">
            <x-location-selector :mobileNumber="true" :col="6" :countryId="$user->country_id ?? 101"
                :stateId="$user->state_id ?? 4021" :cityId="$user->city_id ?? 131821" />
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="industry_type" class="form-label">Industry Type</label>
                <span class="text-danger">*</span></label>
                <select name="industry_type" id="industry_type" class="form-control" required>
                    <option value="">-- Select Industry Type --</option>
                    @foreach(\App\Models\User::INDUSTRY_TYPES as $key => $label)
                        <option value="{{ $key }}" {{ old('industry_type', $user->industry_type ?? '') == $key ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
                @error('industry_type')
                    <div class="text-danger text-sm">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6 mb-3">
                <label for="gender" class="form-label">Gender</label>
                <span class="text-danger">*</span></label>
                <select name="gender" id="gender" class="form-control" required>
                    <option value="">-- Select Gender Type --</option>
                    @foreach(\App\Models\User::GENDER as $key => $label)
                        <option value="{{ $key }}" {{ old('gender', $user->gender ?? '') == $key ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
                @error('gender')
                    <div class="text-danger text-sm">{{ $message }}</div>
                @enderror
            </div>

        </div>

        <!-- Password -->
        <div class="row">

            <div class="col-md-6 mb-3">
                <x-input-label for="password" :value="__('Password')" />
                <span class="text-danger">*</span></label>
                <x-text-input id="password" class="block mt-1 w-full form-control" type="password" name="password"
                    required autocomplete="new-password" />

                <!-- <x-input-error  :messages="$errors->get('password')" class="mt-2 text-danger" /> -->
                @error('password')
                    <div class="text-danger text-sm">{{ $message }}</div>
                @enderror
            </div>

            <!-- Confirm Password -->
            <div class="col-md-6 mb-3">
                <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
                <span class="text-danger">*</span></label>
                <x-text-input id="password_confirmation" class="block mt-1 w-full form-control" type="password"
                    name="password_confirmation" required autocomplete="new-password" />

                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
            </div>
        </div>

        <div class="col-md-12 mb-3">
            <div class="d-flex gap-5">
                <div class="form-check">
                    <input class="form-check-input" value="experienced" type="radio" name="experience_type"
                        id="experienced" checked required>
                    <label class="form-check-label" for="experienced">
                        I'm experienced
                    </label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" value="fresher" type="radio" name="experience_type" id="fresher"
                        required>
                    <label class="form-check-label" for="fresher">
                        I'm a fresher
                    </label>
                </div>
            </div>
            @error('experience_type')
                <div class="text-danger text-sm">{{ $message }}</div>
            @enderror
        </div>

        <div class="flex items-center justify-end">
            @if (Route::has('login'))
                <div class="d-flex justify-content-end mt-2 mb-2">
                    <a class="text-decoration-none text-muted" href="{{ route('login') }}">
                        {{ __('Already registered?') }}
                    </a>
                </div>
            @endif

            <div class="form-group d-flex justify-content-center mt-3">
                <x-primary-button class="sign_inButton w-75 text-center text-decoration-none">
                    {{ __('Register Now') }}
                </x-primary-button>
            </div>
        </div>
    </form>
</x-guest-layout>
