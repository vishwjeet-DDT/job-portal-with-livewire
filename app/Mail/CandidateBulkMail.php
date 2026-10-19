<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\JobApplication;

class CandidateBulkMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
public function __construct(
        public $template,
        public JobApplication $application
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
            view: 'emails.candidate-bulk-mail',
            with: [
                'candidateName' => $this->application->user->name,
                'jobTitle'      => $this->application->job->title,
                'body'          => $this->replacePlaceholders($this->template->body),
                'companyName'   => $this->application->job->company_name,
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
                '{{application_date}}',
            ],
            [
                $this->application->user ? $this->application?->user->name : 'test' ,
                $this->application->job->title,
                $this->application->job->company_name,
                $this->application->created_at->format('d M Y'),
            ],
            $text
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
