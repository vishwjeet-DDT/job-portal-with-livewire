@props([
    'id' => 'livewireModal',
    'title' => 'Modal',
    'modal_size' => 'lg'
])

<div wire:ignore.self class="modal fade" id="{{ $id }}" tabindex="-1" aria-labelledby="{{ $id }}Label" aria-bs-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-{{$modal_size}} modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="{{ $id }}Label">{{ $title }}</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-bs-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <hr class="m-0">

            <div class="modal-body p-2">
                {{ $slot }}
            </div>

        </div>
    </div>
</div>


@push('js')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        $('#{{ $id }}').on('shown.bs.modal', function () {
            $(this).find('.select2').select2({
                dropdownParent: $('#{{ $id }}'),
                width: '100%'
            });
        });

        // Reinitialize on Livewire updates
        Livewire.hook('message.processed', (message, component) => {
            $('#{{ $id }} .select2').select2({
                dropdownParent: $('#{{ $id }}'),
                width: '100%'
            });
        });
    });

</script>


@endpush

@push('css')
<style>
.select2-container--default .select2-selection--single {
    height: 38px !important;
    padding: 6px 12px;
    border: 1px solid #ced4da;
    border-radius: 0.375rem;
}
.select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 24px;
}
.select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 36px;
    right: 10px;
}
</style>
@endpush

