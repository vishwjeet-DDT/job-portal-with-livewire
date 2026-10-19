<x-form-layout :title="$isEdit ? 'Edit User' :'Create '. $usertype ?? 'admin'" :is-edit="$isEdit" back-route="{{ route('admin.user_list', $usertype ?? 'admin') }}">
 <x-dynamic-form :fields="$formFields" form-data="formData"
  :col="($usertype ?? '') === 'Candidates' ? 3 : 2" />


</x-form-layout>

@push('js')
<script>
$(document).ready(function () {
    $('#createModal .select2').select2({
        dropdownParent: $('#createModal'),
        width: '100%'
    });
});

document.addEventListener('DOMContentLoaded', function () {
    $('#createModal .select2').on('change', function (e) {
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
