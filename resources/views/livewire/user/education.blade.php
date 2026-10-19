<div>

    <!-- Header -->


    <div class="card mb-3">
        <div class="card-body">
             <div class="d-flex justify-content-between align-items-center">
                <span>
                    <strong>Education</strong>
                </span>
                <a wire:click="openModal" class="add_profile_details text-decoration-none" style="cursor:pointer">
                    Add Education
                </a>
            </div>
            <table class="table skillTable">
                <thead class="">
                    <tr>
                        <th>Course</th>
                        <th>University</th>
                        <th>Type</th>
                        <th>From</th>
                        <th>To</th>
                        <th style="width: 120px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($educations as $edu)
                        <tr>
                            <td>{{ $edu->course }}</td>
                            <td>{{ $edu->university }}</td>
                            <td>{{ $edu->course_type }}</td>
                            <td>{{ $edu->from_year }}</td>
                            <td>{{ $edu->to_year }}</td>
                            <td class="d-flex gap-2">
                                <button wire:click="edit({{ $edu->id }})"
                                    class="btn btn-link p-0 text-primary">Edit</button>
                                <button wire:click="delete({{ $edu->id }})"
                                    class="btn btn-link p-0 text-danger">Delete</button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-3">No education added</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>


    <!-- Modal -->
    @if($modalOpen)
        <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">

                    <div class="modal-header">
                        <h5 class="modal-title">
                            {{ $editId ? 'Edit Education' : 'Add Education' }}
                        </h5>
                        <button type="button" class="btn-close" wire:click="$set('modalOpen', false)"></button>
                    </div>

                    <div class="modal-body">

                        <div class="mb-3">
                            <label class="form-label">Course *</label>
                            <input type="text" wire:model="course" class="form-control">
                            @error('course') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">University/Institute *</label>
                            <input type="text" wire:model="university" class="form-control">
                            @error('university') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Course Type *</label>
                            <div class="d-flex gap-3 mt-1">

                                <div class="form-check">
                                    <input type="radio" wire:model="course_type" value="Full Time" class="form-check-input"
                                        id="fullTime">
                                    <label for="fullTime" class="form-check-label">Full Time</label>
                                </div>

                                <div class="form-check">
                                    <input type="radio" wire:model="course_type" value="Part Time" class="form-check-input"
                                        id="partTime">
                                    <label for="partTime" class="form-check-label">Part Time</label>
                                </div>

                                <div class="form-check">
                                    <input type="radio" wire:model="course_type" value="Distance" class="form-check-input"
                                        id="distance">
                                    <label for="distance" class="form-check-label">Distance Learning</label>
                                </div>

                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">From *</label>
                                <select wire:model="from_year" class="form-select">
                                    <option value="">Select Starting Year</option>
                                    @foreach(range(date('Y'), 1970) as $year)
                                        <option value="{{ $year }}">{{ $year }}</option>
                                    @endforeach
                                </select>
                                @error('from_year') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">To *</label>
                                <select wire:model="to_year" class="form-select">
                                    <option value="">Select Ending Year</option>
                                    @foreach(range(date('Y'), 1970) as $year)
                                        <option value="{{ $year }}">{{ $year }}</option>
                                    @endforeach
                                </select>
                                @error('to_year') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>

                        </div>

                    </div>

                    <div class="modal-footer">
                        <button wire:click="$set('modalOpen', false)" class="btn btn-secondary">Cancel</button>
                        <button wire:click="save" class="btn btn-primary">Save</button>
                    </div>

                </div>
            </div>
        </div>
    @endif

</div>
