<?php

namespace App\Livewire\Job;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Job;


class JobList extends Component
{
    use WithPagination;

    public $search = '';
    public $col = 3;
    protected $paginationTheme = 'bootstrap';

    protected $queryString = ['search'];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $jobs = Job::with(['cities', 'createdBy', 'company'])
            ->when($this->search, function ($query) {
                $query->where('title', 'LIKE', "%{$this->search}%")
                    ->orWhereHas('cities', function ($q) {
                        $q->where('name', 'LIKE', "%{$this->search}%");
                    })
                    ->orWhereHas('company', function ($q) {
                        $q->where('company_name', 'LIKE', "%{$this->search}%");
                    });
            })
            ->latest()
            ->paginate(10);

        return view('livewire.job.job-list', [
            'jobs' => $jobs
        ]);
    }
}
