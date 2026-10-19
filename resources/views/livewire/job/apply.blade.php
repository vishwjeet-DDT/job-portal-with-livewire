<div>
    <button
        wire:click="apply"
        class="button applyButton text-white applyNow"
        @if($alreadyApplied) disabled @endif
    >
        {{ $alreadyApplied ? 'Applied' : 'Apply' }}
    </button>

    @if (session()->has('success'))
        <div class="text-success mt-2">
            {{ session('success') }}
        </div>
    @endif
</div>
