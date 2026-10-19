<?php

namespace App\Livewire\User;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Traits\HasDynamicForm;
use Illuminate\Support\Facades\Storage;
use App\Models\{User,Country,State,City};
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;

class Edit extends Component
{

     use HasDynamicForm, WithFileUploads;

    public bool $isEdit = false;
    public $usertype;
    public array $formData = [];
    public $id;
    public $user;

    public $states = [];
    public $cities = [];

    protected $listeners = ['setData'];

    public function mount($usertype =null,$user=null)
    {
        $this->user = $user;
        $this->usertype = $usertype;
        $this->buildForm();
        if($user)
        {
            $this->loadUser($user?->id);
        }
        // dd($this->id);
    }

    private function buildForm()
    {
        if ($this->usertype == 'Candidates') {
            $excludeColumns = ['name', 'status'];

        } else {
            $excludeColumns = [
                'profile_img', 'experience_type', 'industry_type',
                'name', 'status', 'country_id', 'city_id', 'state_id', 'gender'
            ];
        }

        $this->initializeFormFields(
            tableName: 'users',
            excludeColumns: $excludeColumns,
            customFields: [
                'profile_img' => [
                    'label' => 'Upload Profile',
                    'type' => 'file',
                    'required' => false,
                    'accept' => '.jpg,.jpeg,.png',
                ]
            ],
            selectOptions: [
                'country_id' => Country::pluck('name', 'id')->toArray(),
                'state_id'   => $this->states ?? [],
                'city_id'    => $this->cities ?? [],
                'industry_type' => User::INDUSTRY_TYPES,
                'gender' => User::GENDER,
                'experience_type' => User::EXPERIENCE_TYPES
            ]
        );
    }

    public function updatedFormDataCountryId($value)
    {
        $this->states = State::where('country_id', $value)->pluck('name', 'id')->toArray();
        $this->state_id = null;
        $this->cities = [];
        $this->city_id = null;
        $this->buildForm();
        $this->dispatch('refresh-select2', id: 'editModal');
    }
    public function updatedFormDataStateId($value)
    {
        $this->cities = City::where('state_id', $value)->pluck('name', 'id')->toArray();
        $this->city_id = null;
        $this->buildForm();
        $this->dispatch('refresh-select2', id: 'editModal');
    }
    public function setData($data)
    {
        $id = $data['id'] ?? null;

        if ($id) {
            $this->loadUser($id);
            $this->dispatch('modal-show', id: 'editModal');
        }
    }

    public function loadUser($id)
    {
        $this->user = User::findOrFail($id);
        $this->id = $id;
        $this->states = State::where('country_id', $this->user->country_id)->pluck('name', 'id')->toArray();

        $this->cities = City::where('state_id', $this->user->state_id)->pluck('name', 'id')->toArray();



        $this->formData = $this->user->toArray();
        $this->buildForm();
    }

    public function updatedFormDataProfileImg()
    {
        $this->dispatch('refresh-select2', id: 'editModal');
    }

    public function rules()
    {
        $rules = [];
        if ($this->usertype == 'Candidates') {
            $rules = [
            'formData.country_id' => 'required|exists:countries,id',
            'formData.state_id' => 'required|exists:states,id',
            'formData.city_id' => 'required|exists:cities,id',
            'formData.mobile_number' =>'required',
            'formData.gender' => ['required'],
            'formData.industry_type' => ['required'],
            'formData.experience_type' => ['required'],
            ];
        }
        return
        array_merge($rules,
            [
            'formData.first_name' => 'required|string|max:255',
            'formData.last_name' => 'required|string|max:255',
            'formData.email' => 'required|string|email|max:255|unique:users,email,' . $this->id,
            'formData.password' => 'nullable|string|min:6',
            'formData.password_confirmation' => 'nullable|string|min:6|same:formData.password',
        ]);
    }


    public function save()
    {
        $this->dispatch('refresh-select2', id: 'editModal');
        // dd($this->formData);
        $this->validate();
        $user = User::find($this->id);
        if(!isset($this->formData['password']) && !$this->formData['password']){
            unset($this->formData['password']);
        }
        unset($this->formData['password_confirmation']);


        if (isset($this->formData['profile_img']) && $this->formData['profile_img'] && is_object($this->formData['profile_img'])) {
            if($user->profile_img && Storage::disk('user/profile_img')->exists($user->profile_img)){
               Storage::disk('public')->delete($user->profile_img);
            }
            $this->formData['profile_img'] = $this->formData['profile_img']->store('user/profile_img', 'public');
        }else{
            unset($this->formData['profile_img']);
        }


        $user->update($this->formData);

        session()->flash('message', 'User updated successfully!');

        $this->dispatch('modal-hide', id: 'editModal');
        $this->dispatch('refreshTable', id: 'dataTable');
        $this->dispatch('refresh-select2', id: 'editModal');
    }
    public function render()
    {
        return view('livewire.user.edit');
    }
}
