<div class="card p-3">

 @if (session()->has('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    {{ session('success') }}
    <button type="button" class="close btn-close btn-white" data-bs-dismiss="alert" aria-label="Close">
    </button>
</div>
@endif

@if($showEmailModal)
<div class="modal fade show d-block" style="background: rgba(0,0,0,.5);">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Send Bulk Email</h5>
                <button class="btn-close" wire:click="$set('showEmailModal', false)"></button>
            </div>

            <div class="modal-body">
                <label>Select Template <span class="text-danger">*</span></label>
                <select class="form-select"
                        wire:model.live="selectedTemplate">
                    <option value="">Choose template</option>
                    @foreach($templates as $template)
                        <option value="{{ $template->id }}">
                            {{ $template->name }}
                        </option>
                    @endforeach
                </select>

                <div class="form-group mt-3">
                    <label>Subject</label>
                    <input type="text" wire:model="subject" value="{{ $subject }}" class="form-select" placeholder="Subject" />
                </div>
                <div  class="form-group mt-3">
                    <label>Body</label>
                    <textarea id="body" wire:model="body" class="form-control  " placeholder="Enter Body" rows="4" >{{ $body }}</textarea>
                </div>
            </div>

            <div class="modal-footer">
                <button class="btn btn-secondary"
                        wire:click="$set('showEmailModal', false)">
                    Cancel
                </button>

                <button class="btn btn-primary"
                        wire:click="sendBulkEmail">
                    Send Email
                </button>
            </div>

        </div>
    </div>
</div>
@endif
    <!-- GLOBAL SEARCH -->
    <div class="mb-1 row d-flex justify-content-end">
         @if ($selectedApplicants)
            <div class="mb-3  col-9">
                <button class="btn btn-primary"
                        wire:click="openBulkEmailModal">
                    Send Email ({{ count($selectedApplicants) }})
                </button>
            </div>
            @endif
        <div class="col-md-{{ $col ?? 4 }}">
            <input type="text" wire:model.live.debounce.400ms="search" class="form-control border rounded px-3"
                placeholder="Enter skills separated by commas (e.g., Laravel, Vue, Ajax)" style="background:#f4f7fa;">
        </div>
    </div>



    <!-- JOB LIST -->
    <div class="space-y-4">

        <div class="card-body p-0 table-responsive">

            <table class="table tableStyle mb-0 dataTable" id="zero_config_condidates">
                <thead>
                    <tr>
                        <th>
                            <input type="checkbox" wire:model.live="selectAll">
                        </th>
                        <th scope="col">Name</th>
                        <th scope="col" class="text-center">Last Education</th>
                        <th scope="col" class="text-center">Current Company</th>
                        <th scope="col" class="text-center">Resume</th>
                        <th scope="col" class="text-center">Skills</th>
                        <th scope="col" class="text-center">Job Title</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($users as $index => $candidate)
                        <tr>
                            <td>
                                <input type="checkbox"
                                    value="{{ $candidate->id }}"
                                    wire:model.live="selectedApplicants">
                            </td>
                            <td >
                                {{ $candidate->name ?? 'N/A' }}
                            </td>

                            <td class="text-center">
                                {{ $candidate->educations()->first()?->course ?? 'N/A' }}-
                                {{ $candidate->educations()->first()?->university ?? 'N/A' }}
                            </td>
                            <td class="text-center">
                                {{ $candidate->currentEmployment->first()->company_name ?? 'N/A' }}
                            </td>
                            <td class="text-center">
                                @if (!empty($candidate->candidate?->resume))

                                    <a href="{{ asset('storage/'. $candidate->candidate->resume) }}" target="_blank">
                                        View Resume
                                    </a>
                                @endif
                            </td>
                            <td class="text-center">
                               {{ $candidate->skill_name ?? "N/A" }}
                            </td>
                            <td class="text-center">
                                {{ $candidate->job_title ?? "N/A" }}

                            </td>
                        </tr>


                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-3">No Candidate found</td>
                        </tr>
                    @endforelse

                </tbody>
            </table>
        </div>

    </div>
    <div class="mt-4">

         {{ $users->links() }}

    </div>




</div>
