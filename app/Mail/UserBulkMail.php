<?php

namespace App\Mail;

use App\Models\{User, Job};
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class UserBulkMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
   public function __construct(
        public $template,
        public User $user,
        public Job $job
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->replacePlaceholders($this->template->subject),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.user-bulk-mail',
            with: [
                'candidateName' => $this->user->name,
                'jobTitle'      => "title test",
                'body'          => $this->replacePlaceholders($this->template->body),
                'companyName'   => "test companyName ",
                'companyEmail'  => config('mail.from.address'),
            ]
        );
    }

    // Replace dynamic placeholders in subject/body
    private function replacePlaceholders(string $text): string
    {
        return str_replace(
            [
                '{{candidate_name}}',
                '{{job_title}}',
                '{{company_name}}',
                '{{location}}',
                '{{experience}}',
                '{{key_skills}}',
                '{{application_link}}'
            ],
            [
                $this->user ? $this->user->name : 'test' ,
                $this->job ? $this->job->title : "test",
                $this->job ? $this->job->company_name :"test Company",
                $this->job?->cities ? $this->job->cities->pluck('name')->implode(', ') : "Delhi",
                $this->job ? $this->job->experience_level : "mid lavel",
                $this->job?->skills ? $this->job->skills->pluck('name')->implode(', ') : "Laravel" ,
                $this->job ? route('jobs.show', $this->job->slug) : '',
            ],
            $text
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
