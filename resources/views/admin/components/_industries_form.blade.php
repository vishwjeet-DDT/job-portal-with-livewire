<div class="">
    <x-input-label for="name" :value="__('Name')" />
    <span class="text-danger">*</span></label>
    <x-text-input id="name" class="form-control" type="text" name="name" :value="old('last_name')" required
        autofocus autocomplete="name" />

    @error('name')
        <div class="text-danger text-sm">{{ $message }}</div>
    @enderror
</div>
