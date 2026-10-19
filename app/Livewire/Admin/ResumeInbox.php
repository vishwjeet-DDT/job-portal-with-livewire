<?php

namespace App\Livewire\Admin;

use App\Models\ResumeInboxImport;
use App\Services\CandidateResumeImportService;
use App\Services\ResumeInboxService;
use App\Services\ResumeParserService;
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
            $this->actionError = 'Email ko Trash me move nahi kiya ja saka. Mail server ki Trash folder setting check karein.';
        }
    }

    public function saveOne(int $uid, int $index, ResumeInboxService $inbox, ResumeParserService $parser, CandidateResumeImportService $importer): void
    {
        $this->resultSummary = null;
        $this->actionError   = null;

        $result = $this->importOne($uid, $index, $inbox, $parser, $importer);

        $this->resultSummary = [$result];
    }

    public function saveSelected(ResumeInboxService $inbox, ResumeParserService $parser, CandidateResumeImportService $importer): void
    {
        $this->resultSummary = null;
        $this->actionError   = null;

        if (empty($this->selected)) {
            $this->actionError = 'Koi resume select nahi kiya gaya hai.';
            return;
        }

        $results = [];
        foreach ($this->selected as $key) {
            [$uid, $index] = array_map('intval', explode(':', $key, 2));
            $results[] = $this->importOne($uid, $index, $inbox, $parser, $importer);
        }

        $this->selected      = [];
        $this->resultSummary = $results;
    }

    public function saveAllVisible(array $keys, ResumeInboxService $inbox, ResumeParserService $parser, CandidateResumeImportService $importer): void
    {
        $this->resultSummary = null;
        $this->actionError   = null;

        $results = [];
        foreach ($keys as $key) {
            [$uid, $index] = array_map('intval', explode(':', $key, 2));
            $results[] = $this->importOne($uid, $index, $inbox, $parser, $importer);
        }

        $this->selected      = [];
        $this->resultSummary = $results;
    }

    private function importOne(int $uid, int $index, ResumeInboxService $inbox, ResumeParserService $parser, CandidateResumeImportService $importer): array
    {
        $attachment = $inbox->getAttachmentBytes($uid, $index);

        if (!$attachment) {
            return ['filename' => "uid {$uid} #{$index}", 'status' => 'error', 'message' => 'Attachment nahi mil saka (email hata diya gaya ho sakta hai).'];
        }

        $filename = $attachment['filename'];

        if (strlen($attachment['content']) > self::MAX_ATTACHMENT_BYTES) {
            return ['filename' => $filename, 'status' => 'error', 'message' => 'File 5MB se badi hai.'];
        }

        $hash = hash('sha256', $attachment['content']);

        if (ResumeInboxImport::where('attachment_hash', $hash)->exists()) {
            return ['filename' => $filename, 'status' => 'skipped', 'message' => 'Yeh resume pehle se import ho chuka hai.'];
        }

        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $tempPath  = storage_path('app/tmp/resume_inbox_' . $uid . '_' . $index . '_' . uniqid() . '.' . $extension);

        if (!is_dir(dirname($tempPath))) {
            mkdir(dirname($tempPath), 0755, true);
        }

        file_put_contents($tempPath, $attachment['content']);

        try {
            $parsed = $parser->parseFromPath($tempPath, $extension);

            if (empty($parsed) || empty($parsed['raw_text'] ?? null)) {
                return ['filename' => $filename, 'status' => 'error', 'message' => 'Resume se text extract nahi ho saka.'];
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
        } finally {
            if (is_file($tempPath)) {
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
            $connectionError = 'IMAP se connect nahi ho paya: ' . $e->getMessage();
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
