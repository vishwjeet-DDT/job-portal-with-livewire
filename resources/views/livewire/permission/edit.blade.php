<x-form-layout title="Edit Permission" :is-edit="true" back-route="{{ route('admin.permissions.index') }}">
    <x-dynamic-form :fields="$formFields ?? []" form-data="formData" />
</x-form-layout>
