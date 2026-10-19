<x-form-layout padding="4" :title=" 'Edit User' " :is-edit="true" back-route="{{ route('admin.user_list', $usertype ?? 'admin') }}">
    <x-dynamic-form :fields="$formFields" form-data="formData" :col="($usertype ?? '') === 'Candidates' ? 3 : 2"/>
</x-form-layout>

@push('js')

<script>

$(document).ready(function () {
    $('#editModal .select2').select2({
        dropdownParent: $('#editModal'),
        width: '100%'
    });
    $('.select2').select2();
});

document.addEventListener('DOMContentLoaded', function () {

    $('#editModal .select2').on('change', function (e) {
        const model = $(this).data('model');
        const value = $(this).val();

        console.log(model);
        if (model) {
            @this.set(model, value);
            setTimeout(() => {
                console.log('test2');
                $('#editModal .select2').select2({
                    dropdownParent: $('#editModal'),
                    width: '100%'
                });
            }, 500);
        }
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
            }, 500);
        }
    });

    // Phone country code
    const countryInput = document.getElementById('country_code');
    const itiCountry = window.intlTelInput(countryInput, {
        separateDialCode: true,
        initialCountry: "in",
        preferredCountries: ["in", "us", "gb"]
    });

});
</script>
@endpush
