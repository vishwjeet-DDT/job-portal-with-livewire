<div>
<select class="form-control {{ $multiple ? 'select2' : '' }}"  @if($multiple) multiple data-model="selectedSkill" @else wire:model.live="selectedSkill" @endif>
    <option value="">Select Skill</option>

    <option value="add_new">+ Add New Skill</option>

    @foreach($skills as $skill)
        <option value="{{ $skill->id }}" @if(
            ($multiple && in_array($skill->id, $selectedSkill)) ||
            (!$multiple && $selectedSkill == $skill->id)
        ) selected @endif>
            {{ $skill->name }}
        </option>
    @endforeach
</select>


    <!-- Add Skill Modal -->
    <div class="modal fade" id="addSkillModal" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Skill</h5>
                    <button type="button" class="close" data-bs-dismiss="modal">×</button>
                </div>

                <div class="modal-body">
                    <label>Skill Name</label>
                    <input type="text" class="form-control" wire:model="newSkill">
                    <!-- <span class="text-danger skill-error" style="display:none;">
                        Skill name  field is required.
                    </span> -->
                    @error('newSkill')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" wire:click="saveSkill">Save Skill</button>
                </div>
            </div>
        </div>
    </div>
</div>
@push('js')
    <script>
        window.addEventListener('open-skill-modal', function () {
            console.log('addSkillModal');
            $('#addSkillModal').modal('show');
        });

        window.addEventListener('close-skill-modal', function () {
            $('#addSkillModal').modal('hide');
            console.log('close-skill-modal');
            setTimeout(() => {
                $('#skill .select2').val(
                    $('#skill .select2').val().filter(value => value !== 'add_new')
                ).trigger('change');
            }, 300);
        });

        window.addEventListener('close-skill-modal2', function () {
            console.log('close-skill-modal2');
            $('#addSkillModal').modal('hide');
            setTimeout(() => {
                $('#skill .select2').select2();
            }, 500);
        });

        $(document).ready(function () {
            $('#skill .select2').select2();


            $('#skill .select2').on('change', function (e) {
                const model = $(this).data('model');
                const value = $(this).val();
                if (model) {
                    @this.set(model, value);
                    setTimeout(() => {
                        $('#skill .select2').select2();
                    }, 300);
                }
            });
        });
    </script>
@endpush
