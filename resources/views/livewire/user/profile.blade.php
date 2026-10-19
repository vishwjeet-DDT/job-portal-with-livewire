<div>
    <div class="card mb-3">
        <div class="card-body">
            <div class="row">
                <div class="col-md-3 d-flex justify-content-center align-items-center ">
                    <div class="position-relative ">
                        <form id="profileImageUploadForm" enctype="multipart/form-data">
                            <img class="profiles2" src="{{ asset('storage/'.$user->profile_img) ?? ''}}" id="profile_image">
                            <label for="upload_profile_img" class="set_profile_image">
                                <i class="ri-camera-line"></i>
                            </label>
                            <input type="file" class="d-none" wire:model="photo" accept="image, jpg, png"
                                alt="image upload" id="upload_profile_img">
                        </form>
                    </div>
                </div>
                <div class="col-md-9">
                    <div class="profile_detaols2">
                        <div class="userName2 d-flex align-items-center gap-3">
                            <div class="flex-grow-1">
                                <h1 class="m-0 text-start">{{$user->name ?? ''}}</h1>
                                <p class="text-muted mb-0">Profile last updated: <span class="text-dark">{{ $user->updated_at->diffForHumans() }}</span></p>
                            </div>
                            <div>
                                <button class="bg-transparent border-0" wire:click="openEditModal">
                                    <i class="ri-pencil-line fs-5"></i>
                                </button>
                            </div>
                        </div>
                        <hr class="hr" style="width: 40%;">
                        <div class="d-flex gap-3">
                            <div class="d-flex flex-column gap-3 border2">

                                <span class="d-felx gap-3 text-muted textmuteds">
                                    <i class="ri-map-pin-line"></i>
                                    {{$user->city->name ?? 'City'}},
                                    {{$user->state->name ?? 'State'}},
                                    {{$user->country->name ?? 'Country'}}
                                </span>

                                <span class="d-flex gap-1 text-muted">
                                    <i class="ri-briefcase-line"></i>
                                    {{ $user->experience_type ?? '' }}
                                </span>
                            </div>

                            <div class="d-flex flex-column gap-3 border2">
                                <span class="d-felx gap-3 text-muted textmuteds">
                                    <i class="ri-phone-line"></i>
                                    <span class="">
                                        {{$user->mobile_number ?? ''}}
                                    </span>
                                </span>

                                <span class="d-felx gap-3 text-muted textmuteds">
                                    <i class="ri-shopping-bag-line"></i>
                                    <span>{{$user->candidate?->availability ? 'Available within '.$user->candidate->availability.' days' : ' Availability not specified' }}</span>
                                    | <span class="">
                                        {{ \App\Models\User::INDUSTRY_TYPES[$user->industry_type] ?? '' }}
                                    </span>

                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


<!-- ================= EDIT PROFILE MODAL ================= -->

<div  class="modal fade @if($showEditModal) show d-block @endif"
     tabindex="-1"
     style="background: rgba(0,0,0,0.5);"
     @if(!$showEditModal) aria-hidden="true" @endif id="createModal">

    <div class="modal-dialog modal-dialog-centered modal-lg" >
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Basic details</h5>
                <button type="button" class="btn-close"
                        wire:click="$set('showEditModal', false)">
                </button>
            </div>

            <div class="modal-body">

                <div class="row g-1">

                    <div class="col-md-6">
                        <label class="form-label">First Name <span class="text-danger">*</span></label>
                        <input type="text" wire:model="first_name" class="form-control">
                        @error('first_name') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Last Name <span class="text-danger">*</span></label>
                        <input type="text" wire:model="last_name" class="form-control">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Mobile Number <span class="text-danger">*</span></label>
                        <input type="text" wire:model="mobile_number" class="form-control">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="text" wire:model="email" class="form-control">
                    </div>

                    {{-- Country, State, City --}}
                    <div class="col-md-6 mt-3">
                        <div class="form-group">
                        <label>Country <span class="text-danger">*</span></label>
                        <select wire:model.live="country_id" class="form-control">
                            <option value="">Select Country</option>
                            @foreach(App\Models\Country::all() as $country)
                                <option value="{{ $country->id }}" @selected($user['country_id'] == $country->id)>{{ $country->name }}</option>
                            @endforeach
                        </select>
                        </div>
                        @error('country_id') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>

                    <div class="col-md-6 mt-3">
                        <label>State <span class="text-danger">*</span></label>
                        <select wire:model.live="state_id" class="form-control select2">
                            <option value="">Select State</option>
                            @foreach($states as $state)
                                <option value="{{ $state->id }}" @selected($user['state_id'] == $state->id)>{{ $state->name }}</option>
                            @endforeach
                        </select>
                        @error('state_id') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>

                    <div class="col-md-6 mt-3" >
                        <label>City <span class="text-danger">*</span></label>
                        <select wire:model="city_id" class="form-control select2">
                            <option value="">Select City</option>
                            @foreach($cities as $city)
                                <option value="{{ $city->id }}" @selected($user['city_id'] == $city->id)>{{ $city->name }}</option>
                            @endforeach
                        </select>
                        @error('city_id') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Gender <span class="text-danger">*</span></label>
                        <select wire:model="gender" class="form-control">
                            <option value="">Select</option>
                            @foreach(\App\Models\User::GENDER as $key => $label)
                                <option value="{{ $key }}" {{ old('gender', $user->gender ?? '') == $key ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        @error('gender') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Add availability to join <span class="text-danger">*</span></label>
                        <select wire:model="availability"  class="form-control">
                            <option>-- Select Availability--</option>
                            <option value="15"@if($availability == 15)selected @endif>
                                15 Days
                            </option>
                            <option value="30" @if($availability == 30)selected @endif>
                                1 Month
                            </option>
                            <option value="60" @if($availability == 60)selected @endif>
                                2 Months
                            </option>
                            <option value="90" @if($availability == 90)selected @endif>
                                3 Months
                            </option>
                        </select>
                        @error('availability') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Industry Type <span class="text-danger">*</span></label>
                        <select wire:model.live="industry_type" class="form-control">
                            <option value="">-- Select Industry Type --</option>
                            @foreach(\App\Models\User::INDUSTRY_TYPES as $key => $label)
                                <option value="{{ $key }}" {{ old('industry_type', $user->industry_type ?? '') == $key ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        @error('industry_type') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>

                    <div class="col-md-12 mt-3">
                        <label class="form-label">Experience Status <span class="text-danger">*</span></label><br>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" wire:model="experience_type" value="experienced">
                            <label class="form-check-label">I'm experienced {{ $experience_type  }}</label>
                        </div>

                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" wire:model="experience_type" value="fresher">
                            <label class="form-check-label">I'm a fresher</label>
                        </div>
                        <br>
                        @error('experience_type') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>

                </div>

            </div>

            <div class="modal-footer">
                <button class="btn btn-secondary" wire:click="$set('showEditModal', false)">Cancel</button>
                <button class="btn btn-primary" wire:click="saveProfile">Save</button>
            </div>

        </div>
    </div>

</div>
@push('js')
<script>
    $(document).ready(function () {
        $('#createModal .select2').select2({
            dropdownParent: $('#createModal'),
            width: '100%'
        });
    });
</script>

@endpush
