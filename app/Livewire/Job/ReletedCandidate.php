<?php

namespace App\Livewire\Job;

use Livewire\Component;
use App\Models\{EmailTemplate,User};
use Livewire\WithPagination;
use App\Models\JobApplication;
use App\Jobs\SendBulkEmailUser;
use Illuminate\Bus\Batch;
use Illuminate\Support\Facades\Bus;

class ReletedCandidate extends Component
{

    use WithPagination;

    public $selectedApplicants = [];
    public $selectAll = false;
    public $showEmailModal = false;
    public $selectedTemplate = null;
    public $subject = '';
    public $body = '';
    public $templates = [];

    public $search = '';
    public $col = 3;

    public $job;
    public $openNotes = [];

    public $showNoteModal = false;
    public $noteText = '';
    public $selectedCandidateId = null;

    protected $queryString = ['search'];
    protected $paginationTheme = 'bootstrap';
    public $currentPage;

    public $scheduledModel =false;
    public $skills;

    public $jobId;

    public $userId;
    public $template = '';
    public $usersData;

    public function mount($job)
    {
        $this->templates = EmailTemplate::all();
        $this->currentPage = $this->getPage();

        $this->job = $job;
        // dd($this->applicants);
    }

    public function updatedSelectAll($value)
    {

        if ($value) {
            $this->selectedApplicants = $this->usersData->pluck('id')->toArray();
        } else {
            $this->selectedApplicants = [];
        }
    }

    public function updatedSelectedTemplate($value)
    {
        if($value)
            {
              $this->template =  EmailTemplate::find($value);
              $this->subject = $this->template->subject ?? '';
              $this->body = $this->template->body ?? '';
            }
    }

    public function sendBulkEmail()
    {
        $this->validate([
            'selectedApplicants' => 'required|array|min:1',
            'selectedTemplate'   => 'required',
            ]);

            $user = User::whereIn('id', $this->selectedApplicants)
            ->get();

        $jobs = $user->map(fn($user) =>
            new SendBulkEmailUser($user, $this->job, $this->template)
        )->toArray();

        Bus::batch($jobs)
            ->name('Bulk Email - Job ' . $this->job->id)
            ->allowFailures()   // continue even if some fail
            ->finally(function (Batch $batch) {
                \Log::info("Bulk email batch {$batch->id} completed.
                    Failed: {$batch->failedJobs}");
            })
            ->dispatch();

        $this->reset(['selectedApplicants', 'selectAll', 'showEmailModal', 'selectedTemplate']);

        $this->dispatch('success', message: 'Emails are being sent in background!');
    }

    public function openBulkEmailModal()
    {
        $this->showEmailModal = true;
    }
    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {

        $title = $this->job?->title ?? '';
        $searchSkills = array_filter(array_map('trim', explode(',', (string) $this->search)));

        $jobSkills = $this->job?->skills()->pluck('name')->toArray() ?? [];

        $skills = array_unique(array_merge($searchSkills, $jobSkills));
        //  dd($skills);
        $query = User::with(['skills', 'employments'])
                    ->whereHas('skills', fn ($q) => $q->whereIn('name', $skills))
                    ->OrwhereHas('employments', fn ($q) => $q->where('job_title', 'like', "%{$title}%"));
        $users = $query->paginate(10);

        $this->usersData = $query->get();
                    // dd($title,$skills,$users);
        return view('livewire.job.releted-candidate',compact('users'));
    }
}
