<div>
    <div class="card mb-3">
        <div class="card-body">
            <div class="border border-secondary border-dashed rounded p-4 text-center"
                style="border-style: dashed !important;">

                <p class="h5 font-weight-bold mb-2">
                    Already have a resume?
                    <label for="resume-upload" class="text-primary" style="cursor: pointer;">
                        Upload resume
                    </label>
                </p>

                <input type="file" id="resume-upload" class="d-none" wire:model="resume">

                <p class="text-muted small">
                    Supported Formats: doc, docx, rtf, pdf — up to 2 MB
                </p>

                @error('resume')
                    <p class="text-danger small mt-2">{{ $message }}</p>
                @enderror

                {{-- Show Upload Button if file selected --}}
                @if ($resume)
                    <div class="mt-3">
                        <button wire:click="uploadResume" class="btn btn-primary btn-sm">
                            Upload Now
                        </button>
                    </div>
                @endif

                {{-- Show Resume View Button if already uploaded --}}
                @if ($uploadedResume)
                    <div class="mt-3">
                        <a href="{{ asset('storage/' . $uploadedResume) }}" target="_blank" class="btn btn-success btn-sm">
                            View Resume
                        </a>
                    </div>
                @endif

                {{-- Success Message --}}
                @if (session()->has('success'))
                    <p class="text-success small mt-2">
                        {{ session('success') }}
                    </p>
                @endif

            </div>
        </div>
    </div>
</div>
