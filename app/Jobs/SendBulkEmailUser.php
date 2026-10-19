<?php

namespace App\Jobs;

use App\Mail\UserBulkMail;
use App\Models\{User,Job};
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendBulkEmailUser implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Batchable;

    public int $tries = 3;
    public int $timeout = 120;
    public int $backoff = 60;

      public function __construct(
        public User $user,
        public Job $jobModel,
        public $template
    ) {}

    public function handle(): void
    {
        // Skip if batch has been cancelled
        if ($this->batch() && $this->batch()->cancelled()) {
            return;
        }

        // Guard: make sure candidate + email exist
        if (! $this->user || ! $this->user->email) {
            Log::warning("SendBulkEmailJob: Skipping user ID {$this->user->id} — no user email found.");
            return;
        }

        Mail::to($this->user->email)
            ->send(new UserBulkMail($this->template, $this->user, $this->jobModel));

        Log::info("Bulk email sent successfully to {$this->user->email} for user ID {$this->user->id}");
    }

    public function failed(\Throwable $e): void
    {
        Log::error("SendBulkEmailJob failed for user ID {$this->user->id}", [
            'candidate_email' => $this->user->email ?? 'N/A',
            'job_title'       =>  'N/A',
            'error'           => $e->getMessage(),
            'trace'           => $e->getTraceAsString(),
        ]);
    }

    // Unique job per user to prevent duplicate emails
    public function uniqueId(): string
    {
        return 'bulk_email_' . $this->user->id;
    }

    // Tag for Laravel Horizon monitoring
    public function tags(): array
    {
        return [
            'bulk-email',
            'user:' . $this->user->id,
            'candidate:' . ($this->user->id ?? 'unknown'),
        ];
    }
}
