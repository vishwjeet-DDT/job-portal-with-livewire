<?php

namespace App\Livewire\User;

use Livewire\Component;
use App\Models\Employment As Emp;
use Illuminate\Support\Facades\Auth;

class Employment extends Component
{

    public $employments = [];

    public $employment_id;
    public $company_name;
    public $job_title;
    public $joining_date;
    public $is_current = "yes";
    public $expected_notice = "15 Days or Less";
    public $end_date;

    public $mode = "create";
    public $user;

    protected $rules = [
        'company_name' => 'required|string|max:255',
        'job_title' => 'required|string|max:255',
        'joining_date' => 'required|date',
        'is_current' => 'required',
        'expected_notice' => 'required_if:is_current,yes',
        'end_date' => 'required_if:is_current,no|date',
    ];

    protected $listeners =['openEmModal'];
    public $modalOpen =false;

    public function openEmModal()
    {
        $this->modalOpen = true;
    }

    public function updatedIsCurrent($value)
    {
        $this->is_current = $value;
    }

    public function closeModel()  {
        $this->modalOpen = false;
    }

    public function mount($user)
    {
        $this->user = $user;
        $this->loadEmployment();
    }

    public function loadEmployment()
    {
        $this->employments = Emp::where('user_id', $this->user?->id)->get();
    }

    public function resetFields()
    {
        $this->employment_id = null;
        $this->company_name = "";
        $this->job_title = "";
        $this->joining_date = "";
        $this->is_current = "yes";
        $this->expected_notice = "15 Days or Less";
        $this->end_date ='';
    }

    public function openAddModal()
    {
        $this->mode = "create";
        $this->resetFields();
        $this->modalOpen = true;
        $this->dispatch('show-employment-modal');
    }

    public function openEditModal($id)
    {
        $this->mode = "edit";

        $emp = Emp::find($id);

        $this->employment_id = $id;
        $this->company_name = $emp->company_name;
        $this->job_title = $emp->job_title;
        $this->joining_date = $emp->joining_date;
        $this->is_current = $emp->is_current;
        $this->expected_notice = $emp->expected_notice;
        $this->end_date = $emp->end_date;

        $this->modalOpen = true;
        $this->dispatch('show-employment-modal');
    }

    public function saveEmployment()
    {
        $this->validate();

        Emp::create([
            'user_id' => $this->user->id,
            'company_name' => $this->company_name,
            'job_title' => $this->job_title,
            'joining_date' => $this->joining_date,
            'is_current' => $this->is_current,
            'expected_notice' => $this->expected_notice,
            'end_date' => empty($this->end_date) ? null : $this->end_date
        ]);

        $this->loadEmployment();
        session()->flash('success', 'Employment added successfully.');
        $this->modalOpen = false;
        $this->dispatch('hide-employment-modal');
    }

    public function updateEmployment()
    {
        $this->validate();

        Emp::where('id', $this->employment_id)->update([
            'company_name' => $this->company_name,
            'job_title' => $this->job_title,
            'joining_date' => $this->joining_date,
            'is_current' => $this->is_current,
            'expected_notice' => $this->expected_notice,
            'end_date' => empty($this->end_date) ? null : $this->end_date
        ]);

        $this->loadEmployment();
        session()->flash('success', 'Employment updated successfully.');
        $this->modalOpen = false;
        $this->dispatch('hide-employment-modal');
    }

    public function deleteEmployment($id)
    {
        Emp::find($id)->delete();
        $this->loadEmployment();
        session()->flash('success', 'Employment deleted.');
    }
    public function render()
    {
        return view('livewire.user.employment');
    }
}
