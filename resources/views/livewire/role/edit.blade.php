<x-form-layout title="Edit Role" :is-edit="true" back-route="{{ route('admin.roles.index') }}">
    <x-dynamic-form :fields="$formFields" form-data="formData" />
</x-form-layout>
