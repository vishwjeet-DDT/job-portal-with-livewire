<div>
     <div class="card mb-3">
        <div class="card-body">
            <div class="d-flex justify-content-between mb-2">

                <span>
                    <strong>Personal details</strong>
                </span>
                @if (!$details)
                    <a class="add_profile_details text-decoration-none"  wire:click="openModal" style="cursor:pointer">
                        Add Personal details
                    </a>
                @else
                    <a class="text-decoration-none text-dark" wire:click="openModal"  style="cursor:pointer">
                        <i class="ri-pencil-line"></i>
                    </a>
                @endif
            </div>
            @if ($details)

            <div class="row">
                <div class="col-md-4 text-muted"><span>Marital Status</span><br>{{ $marital_status }}</div>
                <div class="col-md-4 text-muted"><span>Have you taken a career break?</span><br>{{ $career_break }}</div>
                <div class="col-md-4"><span>Date of birth</span><br>{{ $date_of_birth }}</div>

                <div class="col-md-4 mt-3 text-muted"><span>Pincode</span><br>{{ $pincode }}</div>
                <div class="col-md-4 mt-3 text-muted"><span>Language proficiency</span><br> {{ $candidate?->languages->pluck('name')->implode(', ') ?? '' }}</div>
                <div class="col-md-4 mt-3 text-muted" ><span>Hometown</span><br>{{ $hometown }}</div>
            </div>
            @endif

        </div>
    </div>

    <!-- Modal -->
    @if($modalOpen)
    <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.4);" id="createDetailsModal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content p-3">

                <div class="d-flex justify-content-between align-items-center">
                    <h4 class="">Add Personal details</h4>
                    <button class="btn btn-light" wire:click="closeModal">✕</button>
                </div>

                <p class="text-muted">This information is important for employers to know you better</p>

                <div class="row">

                    <!-- Career Break -->
                    <div class="col-md-6 mb-3">
                        <label class="">Have you taken a career break? *</label>
                        <div class="mt-2">
                            <label class="me-3"><input type="radio" wire:model="career_break" value="Yes"> Yes</label>
                            <label><input type="radio" wire:model="career_break" value="No"> No</label>
                        </div>
                        @error('career_break') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                    <!-- DOB -->
                    <div class="col-md-6 mb-3">
                        <label class="">Date of birth *</label>
                        <input type="date" wire:model="date_of_birth" class="form-control">
                        @error('date_of_birth') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                    <!-- Marital Status -->
                    <div class="col-md-6 mb-3">
                        <label class="">Marital Status *</label>
                        <select wire:model="marital_status"  class="form-control">
                            <option value="">--Select--</option>
                            <option>Single</option>
                            <option>Married</option>
                            <option>Divorced</option>
                            <option>Widowed</option>
                        </select>
                        @error('marital_status') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                    <!-- Address -->
                    <div class="col-md-6 mb-3">
                        <label class="">Permanent address *</label>
                        <input type="text" wire:model="address" class="form-control">
                        @error('address') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                    <!-- Language -->
                  <div class="col-md-6 mb-3">
                    <label>Language proficiency</label>
                        <select data-model="languages" multiple class="form-control select2" multiple>
                            @foreach (\App\Models\Language::all() as $language)
                                <option value="{{ $language->id }}" @if (in_array($language->id,$languages))
                                    selected
                                @endif>{{ $language->name }}</option>
                            @endforeach
                        </select>
                        @error('languages')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                </div>


                    <!-- Hometown -->
                    <div class="col-md-6 mb-3">
                        <label class="">Hometown *</label>
                        <input type="text" wire:model="hometown" class="form-control">
                        @error('hometown') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                    <!-- Pincode -->
                    <div class="col-md-6 mb-3">
                        <label class="">Pincode *</label>
                        <input type="text" wire:model="pincode" class="form-control">
                        @error('pincode') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                </div>

                <div class="d-flex justify-content-end mt-3">
                    <button class="btn btn-secondary me-2" wire:click="closeModal">Cancel</button>
                    <button class="btn btn-primary" wire:click="save">Save Personal</button>
                </div>

            </div>
        </div>
    </div>
    @endif

    @push('js')
    <script>
        document.addEventListener('openSelect2',function(){
             setTimeout(() => {
                $('#createDetailsModal .select2').select2({
                    dropdownParent: $('#createDetailsModal'),
                    width: '100%'
                });

                 $('#createDetailsModal .select2').on('change', function (e) {
                     console.log('test2');
                     const model = $(this).data('model');
                     const value = $(this).val();
                     console.log(model);
                     if (model) {
                         @this.set(model, value);
                         setTimeout(() => {
                             console.log('test3');
                             $('#createDetailsModal .select2').select2({
                                 dropdownParent: $('#createDetailsModal'),
                                 width: '100%'
                             });
                         }, 500);
                     }
                 });
                  }, 300);
             });


    </script>

    @endpush
</div>
