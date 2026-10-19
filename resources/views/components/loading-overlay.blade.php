@props(['target' => null])

<div {{ $attributes->merge(['class' => 'position-relative']) }}>
    <div
        @if ($target) wire:loading.flex wire:target="{{ $target }}" @else wire:loading.flex @endif
        class="d-none position-absolute top-0 start-0 w-100 h-100 align-items-center justify-content-center"
        style="background: rgba(255,255,255,0.6); z-index: 20;"
    >
        <div class="text-center">
            <div class="spinner-border text-primary mb-2" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <div class="fw-semibold text-muted">Loading...</div>
        </div>
    </div>

    <div
        @if ($target) wire:loading.class="loading-blur" wire:target="{{ $target }}" @else wire:loading.class="loading-blur" @endif
    >
        {{ $slot }}
    </div>
</div>

@once
    <style>
        .loading-blur {
            filter: blur(2.5px);
            opacity: 0.6;
            pointer-events: none;
            user-select: none;
            transition: filter .15s ease, opacity .15s ease;
        }
    </style>
@endonce
