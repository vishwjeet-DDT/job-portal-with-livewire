<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Webklex\IMAP\Facades\Client as ImapClient;
use Webklex\PHPIMAP\Client;
use Webklex\PHPIMAP\Message;

/**
 * IMAP-facing service for the admin Resume Inbox feature. Only ever touches
 * cheap header/attachment-name metadata when listing — attachment bytes are
 * only fetched on demand (preview / save).
 */
class ResumeInboxService
{
    public const ALLOWED_EXTENSIONS = ['pdf', 'doc', 'docx', 'rtf'];

    public function connect(): Client
    {
        return ImapClient::account('default')->connect();
    }

    /**
     * @return array{rows: array, total: int}
     */
    public function listAttachmentRows(string $filter, int $page = 1, int $perPage = 20): array
    {
        $client = $this->connect();
        $folder = $client->getFolder('INBOX');

        $query = $folder->query()->leaveUnread();

        $query = match ($filter) {
            'unread' => $query->whereUnseen(),
            'read'   => $query->whereSeen(),
            default  => $query->whereAll(),
        };

        $messages = $query->get()->sortByDesc(
            fn (Message $m) => optional($m->getDate()->first())->timestamp ?? 0
        );

        $rows = [];

        foreach ($messages as $message) {
            if (!$message->hasAttachments()) {
                continue;
            }

            $fromAddress = $message->getFrom()->first();
            $fromEmail   = $fromAddress ? trim(($fromAddress->mailbox ?: '') . '@' . ($fromAddress->host ?: '')) : '';
            $fromName    = $fromAddress->personal ?? '';

            $index = 0;
            foreach ($message->getAttachments() as $attachment) {
                $filename  = (string) $attachment->getName();
                $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

                if (!in_array($extension, self::ALLOWED_EXTENSIONS, true)) {
                    $index++;
                    continue;
                }

                $rows[] = [
                    'uid'                 => $message->getUid(),
                    'message_id'          => (string) $message->getMessageId(),
                    'subject'             => (string) $message->getSubject(),
                    'from'                => $fromEmail,
                    'from_name'           => $fromName,
                    'date'                => optional($message->getDate()->first()),
                    'seen'                => $message->hasFlag('seen'),
                    'attachment_index'    => $index,
                    'attachment_filename' => $filename,
                    'attachment_size'     => (int) $attachment->getSize(),
                ];

                $index++;
            }
        }

        if ($filter === 'read') {
            $rows = array_slice($rows, 0, 50);
        }

        $total = count($rows);
        $rows  = array_slice($rows, ($page - 1) * $perPage, $perPage);

        return ['rows' => $rows, 'total' => $total];
    }

    /**
     * @return array{filename: string, mime: ?string, content: string}|null
     */
    public function getAttachmentBytes(int $uid, int $attachmentIndex): ?array
    {
        try {
            $client  = $this->connect();
            $folder  = $client->getFolder('INBOX');
            $message = $folder->query()->leaveUnread()->getMessageByUid($uid);
        } catch (\Throwable $e) {
            return null;
        }

        if (!$message->hasAttachments()) {
            return null;
        }

        $attachment = $message->getAttachments()->values()->get($attachmentIndex);

        if (!$attachment) {
            return null;
        }

        return [
            'filename'   => (string) $attachment->getName(),
            'mime'       => $attachment->getMimeType(),
            'content'    => $attachment->getContent(),
            'message_id' => (string) $message->getMessageId(),
        ];
    }

    public function markSeen(int $uid): void
    {
        try {
            $client  = $this->connect();
            $folder  = $client->getFolder('INBOX');
            $message = $folder->query()->leaveUnread()->getMessageByUid($uid);
            $message->setFlag('Seen');
        } catch (\Throwable $e) {
            Log::error('Resume Inbox: mark seen failed', ['uid' => $uid, 'message' => $e->getMessage()]);
        }
    }

    public function moveToTrash(int $uid): bool
    {
        try {
            $client  = $this->connect();
            $folder  = $client->getFolder('INBOX');
            $message = $folder->query()->leaveUnread()->getMessageByUid($uid);

            $trashPath = $this->resolveTrashFolder($client);
            $message->move($trashPath);

            return true;
        } catch (\Throwable $e) {
            Log::error('Resume Inbox: move to trash failed', [
                'uid'     => $uid,
                'message' => $e->getMessage(),
            ]);

            return false;
        }
    }

    private function resolveTrashFolder(Client $client): string
    {
        $configured = config('imap.trash_folder', 'INBOX.Trash');

        try {
            if ($client->getFolder($configured)) {
                return $configured;
            }
        } catch (\Throwable $e) {
            // fall through to auto-detect
        }

        try {
            foreach ($client->getFolders() as $folder) {
                if (str_contains(strtolower($folder->name), 'trash')) {
                    return $folder->path;
                }
            }
        } catch (\Throwable $e) {
            // fall through to configured default
        }

        return $configured;
    }
}
