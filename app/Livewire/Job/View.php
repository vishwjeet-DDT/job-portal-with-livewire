<?php

namespace App\Livewire\Job;

use Livewire\Component;
use App\Models\Job;

class View extends Component
{
    public $job;

    protected $listeners = ['viewJob'];

    public function viewJob($id)
    {
        $this->job = Job::with(['getLanguages', 'cities', 'createdBy'])->where('id',$id)->first();
        $this->dispatch('modal-show', id: 'viewModal');
    }

    public function render()
    {
        // dd($this->job);
        return view('livewire.job.view');
    }
}
