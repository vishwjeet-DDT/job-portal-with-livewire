<x-form-layout title="Create Role">
    <x-dynamic-form :fields="$formFields" form-data="formData" col="1" />
</x-form-layout>

@push('js')
<script>
    document.addEventListener('DOMContentLoaded', function () {
    $('#editModal .select2').on('change', function (e) {
        const model = $(this).data('model');
        const value = $(this).val();
        if (model) {
            @this.set(model, value);
            setTimeout(() => {
                console.log('test2');
                $('#createModal .select2').select2({
                    dropdownParent: $('#createModal'),
                    width: '100%'
                });
            }, 500);
        }
    });
})
</script>
@endpush
