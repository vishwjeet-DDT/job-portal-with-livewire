@props([
    'message' => null,
    'id' => null,
    'title' => null,
    'action' => '#',
    'btn_name' => 'Submit',
    'select_datas' => [],
    'modal_size' => 'lg'
])

<!-- Trigger Button -->
<button type="button"
    class="btn addButton mb-3"
    data-bs-toggle="modal"
    data-bs-target="#modal_{{ $id }}">
    <i class="ri-add-line me-1"></i> {{ $btn_name }}
</button>

<!-- Modal -->
<div class="modal fade" id="modal_{{ $id }}" tabindex="-1" aria-labelledby="modalLabel_{{ $id }}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-{{$modal_size}}">
        <div class="modal-content">

            <!-- Header -->
            <div class="modal-header">
                <h5 class="modal-title" id="modalLabel_{{ $id }}">{{ $title }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
               <hr>
            <!-- Body -->
            <div class="modal-body">
                <form action="{{ $action }}" method="POST" enctype="multipart/form-data" id="form_{{ $id }}" onsubmit="formSubmit2(event, this, '{{ $id }}')">
                    @csrf

                    @if (view()->exists("admin.components._{$id}_form"))
                        @include("admin.components._{$id}_form", ['select_datas' => $select_datas])
                    @else
                        <p class="text-danger">Form not found: <code>admin/components/_{{ $id }}_form.blade.php</code></p>
                    @endif

                    <!-- Footer -->
                    <div class="modal-footer p-0 pt-3">
                        <button type="submit" class="btn btn-primary">{{ $btn_name }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
