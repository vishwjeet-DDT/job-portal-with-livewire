<?php

namespace App\Jobs;

use App\Mail\CandidateBulkMail;
use App\Models\JobApplication;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendBulkEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Batchable;

    public int $tries = 3;
    public int $timeout = 60;
    public int $backoff = 60; // wait 60s between retries

    public function __construct(
        public JobApplication $application,
        public $template
    ) {}

    public function handle(): void
    {
        // Skip if batch has been cancelled
        if ($this->batch() && $this->batch()->cancelled()) {
            return;
        }

        // Guard: make sure candidate + email exist
        if (! $this->application->user || ! $this->application->user->email) {
            Log::warning("SendBulkEmailJob: Skipping application ID {$this->application->id} — no user email found.");
            return;
        }

        Mail::to($this->application->user->email)
            ->send(new CandidateBulkMail($this->template, $this->application));

        Log::info("Bulk email sent successfully to {$this->application->user->email} for application ID {$this->application->id}");
    }

    public function failed(\Throwable $e): void
    {
        Log::error("SendBulkEmailJob failed for application ID {$this->application->id}", [
            'candidate_email' => $this->application->user->email ?? 'N/A',
            'job_title'       => $this->application->job->title ?? 'N/A',
            'error'           => $e->getMessage(),
            'trace'           => $e->getTraceAsString(),
        ]);
    }

    // Unique job per application to prevent duplicate emails
    public function uniqueId(): string
    {
        return 'bulk_email_' . $this->application->id;
    }

    // Tag for Laravel Horizon monitoring
    public function tags(): array
    {
        return [
            'bulk-email',
            'application:' . $this->application->id,
            'candidate:' . ($this->application->user->id ?? 'unknown'),
        ];
    }
}
