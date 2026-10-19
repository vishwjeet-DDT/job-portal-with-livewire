<?php
namespace App\Livewire\User;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;

class ResumeUpload extends Component
{
    use WithFileUploads;

    public $resume;
    public $uploadedResume;
    public $user;

    public function mount($user)
    {
        $this->user =$user;
        $candidate = $user?->candidate;
        $this->uploadedResume = $candidate?->resume;
    }

    public function uploadResume()
    {

        $this->validate([
            'resume' => 'required|mimes:pdf,doc,docx,rtf',
        ]);

        // $filePath = $this->resume->store('resumes', 'public');
        $directory = public_path('storage/resumes');
        if (!file_exists($directory)) {
            mkdir($directory, 0755, true);
        }
        $filename = time() . '_' . $this->resume->getClientOriginalName();
        $tempPath = $this->resume->getRealPath();
        $destinationPath = $directory . '/' . $filename;
        copy($tempPath, $destinationPath);

        $user = $this->user;

        // $user->profile_img = $filePath;
        $candidate = $user->candidate()->firstOrCreate([
            'user_id' => $user->id
        ]);


        $candidate->resume = 'resumes/'.$filename;
        $candidate->save();
        // $user->save();

        $this->uploadedResume = $candidate?->resume;
        $this->reset('resume');

        $this->dispatch('resumeUploaded');

        session()->flash('success', 'Resume uploaded successfully!');
    }

    public function render()
    {
        return view('livewire.user.resume-upload');
    }
}
