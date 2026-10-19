<x-form-layout title="Create Role" padding="3">
    <x-dynamic-form :fields="$formFields" form-data="formData" col="3" multiSelect=1 />
</x-form-layout>


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
@push('js')
<script src="https://cdn.ckeditor.com/ckeditor5/41.0.0/classic/ckeditor.js">
</script>

<script>
let editorInstance;

$(document).ready(function () {
    $('.select2').select2();
});

document.addEventListener('DOMContentLoaded', function () {
let typingTimer;
const doneTypingDelay = 1500;
ClassicEditor
    .create(document.querySelector('#description'))
    .then(editor => {
        editorInstance = editor;
        editor.model.document.on('change:data', () => {
            clearTimeout(typingTimer);
            @this.set('formData.description', editor.getData());
            typingTimer = setTimeout(() => {
                $('.select2').select2();
            }, doneTypingDelay);
        });
    })
    .catch(error => {
        console.error(error);
    });

   $('.select2').on('change', function (e) {
        const model = $(this).data('model');
        const value = $(this).val();
        console.log(model);
        if (model) {
            @this.set(model, value);
            setTimeout(() => {
                console.log('test2');
                $('.select2').select2();
            }, 400);
        }
    });
});

</script>
@endpush
