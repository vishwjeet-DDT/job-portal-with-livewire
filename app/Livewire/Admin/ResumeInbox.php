<?php

namespace App\Livewire\Admin;

use App\Models\ResumeInboxImport;
use App\Services\CandidateResumeImportService;
use App\Services\ResumeInboxService;
use App\Services\ResumeParserService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Livewire\Attributes\Renderless;
use Livewire\Component;

class ResumeInbox extends Component
{
    public string $filter = 'unread'; // all|read|unread
    public int $page = 1;
    public int $perPage = 15;
    public array $selected = []; // composite keys "uid:attachment_index"
    public ?array $resultSummary = null;
    public ?string $actionError = null;

    private const MAX_ATTACHMENT_BYTES = 5 * 1024 * 1024; // 5MB

    public function updatingFilter(string $value): void
    {
        $this->page = 1;
        $this->selected = [];
        $this->perPage = $value === 'read' ? 10 : 15;
    }

    public function gotoPage(int $page): void
    {
        $this->page = max(1, $page);
        $this->selected = [];
    }

    public function selectAllVisible(array $keys): void
    {
        $this->selected = array_values(array_unique(array_merge($this->selected, $keys)));
    }

    public function clearSelection(): void
    {
        $this->selected = [];
    }

    public function removeEmail(int $uid, ResumeInboxService $inbox): void
    {
        $this->resultSummary = null;
        $this->actionError   = null;

        if (!$inbox->moveToTrash($uid)) {
            $this->actionError = 'Could not move the email to Trash. Please check the mail server Trash folder setting.';
        }
    }

    public function saveOne(int $uid, int $index, ResumeInboxService $inbox, ResumeParserService $parser, CandidateResumeImportService $importer): void
    {
        $this->resultSummary = null;
        $this->actionError   = null;

        $result = $this->importOne($uid, $index, $inbox, $parser, $importer);

        $this->resultSummary = [$result];
    }

    /**
     * Bulk upload is driven from the browser one resume per request (see the
     * Alpine batch runner in the view): each resume needs an IMAP download plus
     * an AI call, so doing a whole page in one request blows past PHP /
     * nginx / load-balancer timeouts in production. One request per resume
     * also means a bad file can never abort the rest of the batch.
     *
     * Renderless: the inbox listing is only re-rendered once, in finishBatch().
     */
    #[Renderless]
    public function importKey(string $key, ResumeInboxService $inbox, ResumeParserService $parser, CandidateResumeImportService $importer): array
    {
        if (!preg_match('/^\d+:\d+$/', $key)) {
            return ['key' => $key, 'filename' => $key, 'status' => 'error', 'message' => 'Invalid selection.'];
        }

        [$uid, $index] = array_map('intval', explode(':', $key, 2));

        return ['key' => $key] + $this->importOne($uid, $index, $inbox, $parser, $importer);
    }

    public function finishBatch(array $results): void
    {
        $this->actionError   = null;
        $this->selected      = [];
        $this->resultSummary = array_values(array_map(fn ($r) => [
            'filename' => (string) ($r['filename'] ?? ''),
            'status'   => in_array($r['status'] ?? '', ['created', 'skipped'], true) ? $r['status'] : 'error',
            'message'  => (string) ($r['message'] ?? 'Unknown error.'),
        ], $results));
    }

    public function refreshInbox(ResumeInboxService $inbox): void
    {
        $this->resultSummary = null;
        $this->actionError   = null;
        $inbox->forgetListing();
    }

    /**
     * Never throws: any failure for one resume (IMAP, corrupt file, parser,
     * AI, DB) is turned into an 'error' result so batch callers continue.
     */
    private function importOne(int $uid, int $index, ResumeInboxService $inbox, ResumeParserService $parser, CandidateResumeImportService $importer): array
    {
        $filename = "Email #{$uid} (attachment " . ($index + 1) . ')';
        $tempPath = null;

        try {
            $attachment = $inbox->getAttachmentBytes($uid, $index);

            if (!$attachment) {
                return ['filename' => $filename, 'status' => 'error', 'message' => 'Attachment not found (the email may have been removed).'];
            }

            $filename = $attachment['filename'];

            if (strlen($attachment['content']) > self::MAX_ATTACHMENT_BYTES) {
                return ['filename' => $filename, 'status' => 'error', 'message' => 'File is larger than 5MB.'];
            }

            $hash = hash('sha256', $attachment['content']);

            if (ResumeInboxImport::where('attachment_hash', $hash)->exists()) {
                return ['filename' => $filename, 'status' => 'skipped', 'message' => 'This resume has already been imported.'];
            }

            $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            $tempPath  = storage_path('app/tmp/resume_inbox_' . $uid . '_' . $index . '_' . uniqid() . '.' . $extension);

            if (!is_dir(dirname($tempPath))) {
                mkdir(dirname($tempPath), 0755, true);
            }

            file_put_contents($tempPath, $attachment['content']);
            unset($attachment['content']);

            $parsed = $parser->parseFromPath($tempPath, $extension);

            if (empty($parsed) || empty($parsed['raw_text'] ?? null)) {
                return ['filename' => $filename, 'status' => 'error', 'message' => 'Could not extract any text from this resume.'];
            }

            $extracted = $importer->extractStructuredData($parsed['raw_text']) ?? $parsed;

            $result = $importer->importCandidate($extracted, $tempPath, $filename, [
                'message_id'          => $attachment['message_id'] ?? null,
                'uid'                 => $uid,
                'attachment_filename' => $filename,
                'attachment_hash'     => $hash,
            ]);

            return [
                'filename' => $filename,
                'status'   => $result['status'],
                'message'  => $result['message'],
            ];
        } catch (\Throwable $e) {
            Log::warning('Resume Inbox: import failed, skipped', [
                'uid'      => $uid,
                'index'    => $index,
                'filename' => $filename,
                'error'    => $e->getMessage(),
            ]);

            return ['filename' => $filename, 'status' => 'error', 'message' => 'Skipped — this file could not be read (' . Str::limit($e->getMessage(), 120) . ').'];
        } finally {
            if ($tempPath && is_file($tempPath)) {
                unlink($tempPath);
            }
        }
    }

    public function render()
    {
        $inbox = app(ResumeInboxService::class);

        try {
            $data          = $inbox->listAttachmentRows($this->filter, $this->page, $this->perPage);
            $connectionError = null;
        } catch (\Throwable $e) {
            $data = ['rows' => [], 'total' => 0];
            $connectionError = 'Could not connect to the mailbox (IMAP): ' . $e->getMessage();
        }

        $rows = $data['rows'];

        foreach ($rows as &$row) {
            $row['key'] = $row['uid'] . ':' . $row['attachment_index'];
        }
        unset($row);

        $imported = [];
        if (!empty($rows)) {
            $messageIds = array_values(array_unique(array_filter(array_column($rows, 'message_id'))));

            $imported = ResumeInboxImport::whereIn('message_id', $messageIds)
                ->get(['message_id', 'attachment_filename'])
                ->map(fn ($r) => $r->message_id . '|' . $r->attachment_filename)
                ->flip()
                ->toArray();
        }

        return view('livewire.admin.resume-inbox', [
            'rows'            => $rows,
            'total'           => $data['total'],
            'totalPages'      => (int) max(1, ceil($data['total'] / $this->perPage)),
            'imported'        => $imported,
            'connectionError' => $connectionError,
        ]);
    }
}
