<?php

namespace App\Livewire\User;

use Livewire\Component;
use App\Models\Education as Ed;
use Illuminate\Support\Facades\Auth;

class Education extends Component
{
     public $educations, $course, $university, $course_type = 'Full Time', $from_year, $to_year;
    public $editId = null;
    public $modalOpen = false;

    public $user;

    protected $rules = [
        'course' => 'required',
        'university' => 'required',
        'course_type' => 'required',
        'from_year' => 'required|digits:4',
        'to_year' => 'required|digits:4'
    ];

    protected $listeners = ['openModal'];

    public function mount($user)
    {
        $this->user = $user;
        $this->loadEducation();
    }

    public function loadEducation()
    {
        $this->educations = Ed::where('user_id', $this->user?->id)->get();
    }

    public function openModal()
    {
        $this->resetForm();
        $this->modalOpen = true;
    }

    public function edit($id)
    {
        $edu = Ed::find($id);
        $this->editId     = $id;
        $this->course     = $edu->course;
        $this->university = $edu->university;
        $this->course_type = $edu->course_type;
        $this->from_year  = $edu->from_year;
        $this->to_year    = $edu->to_year;

        $this->modalOpen = true;
    }

    public function save()
    {
        $this->validate();

        Ed::updateOrCreate(
            ['id' => $this->editId],
            [
                'user_id' => $this->user->id,
                'course' => $this->course,
                'university' => $this->university,
                'course_type' => $this->course_type,
                'from_year' => $this->from_year,
                'to_year' => $this->to_year
            ]
        );

        $this->modalOpen = false;
        $this->resetForm();
        $this->loadEducation();
    }

    public function delete($id)
    {
        Ed::find($id)->delete();
        $this->loadEducation();
    }

    public function resetForm()
    {
        $this->editId = null;
        $this->course = '';
        $this->university = '';
        $this->course_type = 'Full Time';
        $this->from_year = '';
        $this->to_year = '';
    }

    public function render()
    {
        return view('livewire.user.education');
    }
}
