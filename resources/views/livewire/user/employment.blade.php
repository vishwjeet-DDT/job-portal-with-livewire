<div>

    <!-- Flash Message -->
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <!-- Add Button -->


    <!-- Employment List -->
    <div class="card mb-3">
        <div class="card-body">
            <div class="d-flex justify-content-between mb-2">

                <span>
                    <strong>Employment</strong>
                </span>
                <a class="add_profile_details text-decoration-none" wire:click="openAddModal" style="cursor:pointer">
                    Add Employment
                </a>
            </div>

            <table class="table skillTable">
                <thead>
                    <tr>
                        <th>Company</th>
                        <th>Job Title</th>
                        <th>Joining Date</th>
                        <th>Current?</th>
                        <th>Notice</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($employments as $emp)
                        <tr>
                            <td>{{ $emp->company_name }}</td>
                            <td>{{ $emp->job_title }}</td>
                            <td>{{ $emp->joining_date }}</td>
                            <td>{{ ucfirst($emp->is_current) }}</td>
                            <td>{{ $emp->expected_notice }}</td>
                            <td>
                                <button class="btn btn-link p-0 text-primary" wire:click="openEditModal({{ $emp->id }})">Edit</button>
                                <button class="btn btn-link p-0 text-danger" wire:click="deleteEmployment({{ $emp->id }})">Delete</button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center">No Employment Added</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal -->
    @if ($modalOpen)
        <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">

                    <div class="modal-header">
                        <h5 class="modal-title">
                            {{ $mode == 'create' ? 'Add Employment' : 'Edit Employment' }}
                        </h5>
                        <button type="button" class="btn-close" wire:click="closeModel"></button>
                    </div>

                    <div class="modal-body">
                        <p class="text-muted mb-3">Details like job title, company name, etc, help employers understand your work</p>

                        <div class="row g-3">
                            <!-- company -->
                            <div class="col-md-6">
                                <label class="form-label">company name*</label>
                                <input type="text" wire:model="company_name" class="form-control">
                                @error('company_name') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>

                            <!-- job title -->
                            <div class="col-md-6">
                                <label class="form-label">job title*</label>
                                <input type="text" wire:model="job_title" class="form-control">
                                @error('job_title') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>

                            <!-- joining date -->
                            <div class="col-md-6">
                                <label class="form-label">Joining Date *</label>
                                <input type="date" wire:model="joining_date" class="form-control">
                                @error('joining_date') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>

                            <!-- current employment -->
                            <div class="col-md-6">
                                <label class="form-label">Is this your current employment? *</label>
                                <div class="d-flex gap-4">
                                    <label><input type="radio" wire:model.live="is_current" value="yes"> Yes</label>
                                    <label><input type="radio" wire:model.live="is_current" value="no"> No</label>
                                </div>
                            </div>

                            <!-- expected -->
                            @if($is_current == 'yes')
                                <div class="col-md-6">
                                    <label class="form-label">Expected Notice *</label>
                                    <input type="text" wire:model="expected_notice" class="form-control">
                                    @error('expected_notice') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                            @endif

                            <!-- End Date ONLY when is_current = no -->
                            @if($is_current == 'no')
                                <div class="col-md-6">
                                    <label class="form-label">End Date *</label>
                                    <input type="date" wire:model="end_date" class="form-control">
                                    @error('end_date') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button class="btn btn-light" wire:click="closeModel">Cancel</button>

                        @if ($mode == 'create')
                            <button class="btn btn-primary" wire:click="saveEmployment">Save</button>
                        @else
                            <button class="btn btn-primary" wire:click="updateEmployment">Update</button>
                        @endif
                    </div>

                </div>
            </div>
        </div>
    @endif

</div>
