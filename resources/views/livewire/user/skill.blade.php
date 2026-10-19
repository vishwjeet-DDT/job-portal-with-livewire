<div>

    <div class="card mb-3">
        <div class="card-body">
            <div class="d-flex justify-content-between mb-2">

                <span>
                    <strong>Skills</strong>
                </span>
                <a class="add_profile_details text-decoration-none"  wire:click="$dispatch('openSkillModal')" style="cursor:pointer">
                    Add Skill
                </a>
            </div>
            <table class="table skillTable">
                <thead>
                    <tr>
                        <th>Skill</th>
                        <th>Experience Year</th>
                        <th>Experience Month</th>
                        <th width="150">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($userSkills as $item)
                        <tr>
                            <td>{{ $item->name }}</td>
                            <td>{{ $item->pivot?->experience_years }} Years </td>
                            <td>{{ $item->pivot?->experience_months }} Months</td>
                            <td>
                                <button class="btn btn-link p-0 text-primary" wire:click="edit({{ $item->id }})">
                                    Edit
                                </button>
                                |
                                <button class="btn btn-link p-0 text-danger" wire:click="delete({{ $item->id }})">
                                    Delete
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Main Skill Modal -->
@if($modalOpen)
     <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">
                        {{ $editId ? 'Edit Skill' : 'Add Your Skills' }}
                    </h5>
                    <button type="button" class="btn-close" wire:click="closeModel"></button>
                </div>

                <div class="modal-body">

                    <label>Skill *</label>
                    <input wire:model="newSkill" type="text" class="form-control" placeholder="Skill">
                    @error('newSkill') <small class="text-danger">{{ $message }}</small> @enderror

                    <div class="row mt-3">
                        <div class="col-md-6">
                            <label>Experience *</label>

                            <select wire:model="experience_years" class="form-control">
                                <option value="">Select Years</option>
                                @for ($i = 0; $i <= 20; $i++)
                                    <option value="{{ $i }}">{{ $i }}
                                        Year{{ $i > 1 ? 's' : '' }}</option>
                                @endfor
                            </select>
                            @error('experience_years') <small class="text-danger">{{ $message }}</small> @enderror

                        </div>
                        <div class="col-md-6">
                            <label>In Month *</label>

                            <select wire:model="experience_months" class="form-control">
                                <option value="">Select Months</option>
                                <option value="0">0 Month</option>
                                <option value="1">1 Month</option>
                                <option value="2">2 Month</option>
                                <option value="3">3 Month</option>
                                <option value="4">4 Month</option>
                                <option value="5">5 Month</option>
                                <option value="6">6 Month</option>
                                <option value="7">7 Month</option>
                                <option value="8">8 Month</option>
                                <option value="9">9 Month</option>
                                <option value="10">10 Month</option>
                                <option value="11">11 Month</option>
                                <option value="12">12 Month</option>
                            </select>
                            @error('experience_months') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button class="btn btn-secondary" wire:click="closeModel">Cancel</button>
                    <button class="btn btn-primary" wire:click="saveSkill">Save</button>
                </div>

            </div>
        </div>
    </div>
@endif

</div>
