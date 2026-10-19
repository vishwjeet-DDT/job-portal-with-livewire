<div>

       <div class="card mb-3">
        <div class="card-body">
            <div class="d-flex justify-content-between mb-2">

                <span>
                    <strong>Profile Summary</strong>
                </span>
                @if (!$summary)
                    <a class="add_profile_details text-decoration-none"  wire:click="openModal" style="cursor:pointer">
                        Add Profile Summary
                    </a>
                @else
                    <a class="text-decoration-none text-dark" wire:click="editModal"  style="cursor:pointer">
                        <i class="ri-pencil-line"></i>
                    </a>
                @endif
            </div>

             {{ $summary }}

        </div>
    </div>

    @if ($showModal)
    <div class="modal fade show d-block" style="background: rgba(0,0,0,.5);">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">Profile summary</h5>
                    <button type="button" class="btn-close" wire:click="closeModal"></button>
                </div>

                <div class="modal-body">
                    <p class="text-muted">
                        Give recruiters a brief overview of the highlights of your career, key achievements,
                        and career goals to help recruiters know your profile better.
                    </p>

                    <label class="form-label">Add Profile summary <span class="text-danger">*</span></label>
                    <textarea class="form-control" rows="5"
                        wire:model.defer="summary"
                        placeholder="add profile summary.."></textarea>
                    @error('summary')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <div class="modal-footer">
                    <button class="btn btn-secondary" wire:click="closeModal">Cancel</button>
                    <button class="btn btn-primary" wire:click="save">Save</button>
                </div>

            </div>
        </div>
    </div>
    @endif

</div>
