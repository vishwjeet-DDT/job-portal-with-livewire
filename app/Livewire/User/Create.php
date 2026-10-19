<?php

namespace App\Livewire\User;

use Livewire\Component;
use App\Models\{User,Country,State,City};
use Illuminate\Support\Facades\Hash;
use App\Traits\HasDynamicForm;
use Livewire\WithFileUploads;
use Illuminate\Validation\Rule;

class Create extends Component
{

    use HasDynamicForm, WithFileUploads;

    public bool $isEdit = false;
    public $usertype;
    public $states = [];
    public $cities = [];

    public function mount($usertype =null)
    {
        $this->usertype = $usertype;
        $this->buildForm();
    }

    private function buildForm()
    {
        // if ($this->usertype == 'Candidates') {
        //     $excludeColumns = ['name', 'status'];

        // } else {
        //     }
            $excludeColumns = [
                 'experience_type', 'industry_type',
                'name', 'status', 'country_id', 'city_id', 'state_id', 'gender','country_id'
            ];

        $this->initializeFormFields(
            tableName: 'users',
            excludeColumns: $excludeColumns,
            customFields: [
                'profile_img' => [
                    'label' => 'Upload Resume',
                    'type' => 'file',
                    'required' => true,
                    'accept' => '.jpg,.jpeg,.png,pdf',
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
        $this->dispatch('refresh-select2', id: 'createModal');
    }
    public function updatedFormDataStateId($value)
    {
        $this->cities = City::where('state_id', $value)->pluck('name', 'id')->toArray();
        $this->city_id = null;
        $this->buildForm();
        $this->dispatch('refresh-select2', id: 'createModal');
    }

    public function rules()
    {
        $rules = [];
        if ($this->usertype == 'Candidates1') {
            $rules = [
            'formData.country_id' => 'required|exists:countries,id',
            'formData.state_id' => 'required|exists:states,id',
            'formData.city_id' => 'required|exists:cities,id',
            'formData.mobile_number' =>'required',
            'formData.gender' => ['required', Rule::in(User::GENDER)],
            'formData.industry_type' => ['required', Rule::in(User::INDUSTRY_TYPES)],
            'formData.experience_type' => ['required', Rule::in(User::EXPERIENCE_TYPES)],
            ];
        }
        return array_merge($rules,
        $this->getValidationRules(),
        [
            'formData.first_name' => 'required|string|max:255',
            'formData.last_name' => 'required|string|max:255',
            'formData.email' => 'required|string|email|max:255|unique:users,email',
            'formData.password' => 'required|string|min:6',
        ]);
    }

    public function updatedFormDataProfileImg()
    {
        $this->dispatch('refresh-select2', id: 'createModal');
    }

    public function save()
    {
        $this->dispatch('refresh-select2', id: 'createModal');
        $this->validate();
        // dd($this->getCleanFormData(),$this->formData);


        $user = User::create($this->getCleanFormData());
        if($this->usertype){
            $user->assignRole($this->usertype);
        }

        if($this->formData['profile_img']){
            $this->uploadResume($user,$this->formData['profile_img']);
        }
        session()->flash('message', $this->usertype.' created successfully!');
        $this->reset('formData');
        $this->dispatch('modal-hide', id: 'createModal');
        $this->dispatch('refreshTable', id: 'dataTable');
    }
    private function getCleanFormData(): array
    {
        $data = $this->formData;
        unset($data['password_confirmation']);
        if (empty($data['password'])) {
            unset($data['password']);
        }

        // if(!empty($data['profile_img'])){
        //     $imagePath = $data['profile_img']->store('user/profile_img', 'public');
        //     $data['profile_img'] = $imagePath;
        // }
        $data['name'] = $data['first_name'].' '.$data['last_name'];
        $data['status'] = 'active';
        $data['created_by'] = auth()->user()->id ?? null;

        return array_filter($data, fn($value) => $value !== null && $value !== '');
    }

    private function uploadResume($user,$resume)
    {
        $directory = public_path('storage/resumes');
        if (!file_exists($directory)) {
            mkdir($directory, 0755, true);
        }
        $filename = time() . '_' . $resume->getClientOriginalName();
        $tempPath = $resume->getRealPath();
        $destinationPath = $directory . '/' . $filename;
        copy($tempPath, $destinationPath);


        $candidate = $user->candidate()->firstOrCreate([
            'user_id' => $user->id
        ]);


        $candidate->resume = 'resumes/'.$filename;
        $candidate->save();
    }
    public function render()
    {
        return view('livewire.user.create');
    }
}
