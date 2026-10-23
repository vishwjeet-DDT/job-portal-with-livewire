<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Webklex\IMAP\Facades\Client as ImapClient;
use Webklex\PHPIMAP\Client;
use Webklex\PHPIMAP\Folder;
use Webklex\PHPIMAP\Message;

/**
 * IMAP-facing service for the admin Resume Inbox feature. Listing only runs
 * an IMAP SEARCH plus a one-time, cached metadata fetch per new email —
 * attachment bytes are only fetched on demand (preview / save).
 */
class ResumeInboxService
{
    public const ALLOWED_EXTENSIONS = ['pdf', 'doc', 'docx', 'rtf'];

    private const UID_LIST_TTL  = 120; // seconds
    private const META_TTL_DAYS = 30;
    private const FETCH_CHUNK   = 25;

    private ?Client $client = null;
    private ?Folder $folder = null;

    public function connect(): Client
    {
        // One IMAP login per request instead of one per call.
        if ($this->client?->isConnected()) {
            return $this->client;
        }

        return $this->client = $this->openConnection();
    }

    private function openConnection(): Client
    {
        $account = config('imap.accounts.default');

        if (($account['authentication'] ?? null) !== 'oauth') {
            return ImapClient::account('default')->connect();
        }

        // Microsoft 365 rejects basic auth over IMAP: log in with an OAuth token instead.
        $account['password'] = $this->oauthAccessToken();

        return ImapClient::make($account)->connect();
    }

    private function oauthAccessToken(): string
    {
        $oauth = config('imap.oauth');

        return Cache::remember('imap_oauth_token_' . md5($oauth['client_id'] . $oauth['tenant_id']), now()->addMinutes(50), function () use ($oauth) {
            $response = Http::asForm()->post(
                "https://login.microsoftonline.com/{$oauth['tenant_id']}/oauth2/v2.0/token",
                [
                    'grant_type'    => 'client_credentials',
                    'client_id'     => $oauth['client_id'],
                    'client_secret' => $oauth['client_secret'],
                    'scope'         => $oauth['scope'],
                ]
            );

            if (!$response->successful() || !$response->json('access_token')) {
                Log::error('Resume Inbox: OAuth token request failed', ['body' => $response->body()]);
                throw new \RuntimeException('Unable to obtain Microsoft 365 IMAP access token.');
            }

            return $response->json('access_token');
        });
    }

    /**
     * Listing is the hot path (it runs on every Livewire render), so it must
     * not download every email. Only a cheap IMAP SEARCH (UIDs) is done per
     * request; the per-email metadata (subject/from/date/attachment names)
     * is fetched once per UID and cached — IMAP UIDs never change, so cached
     * rows stay valid. New mail costs one fetch, old mail costs nothing.
     *
     * @return array{rows: array, total: int}
     */
    public function listAttachmentRows(string $filter, int $page = 1, int $perPage = 20): array
    {
        $uids = Cache::remember($this->cacheKey("uids_{$filter}"), now()->addSeconds(self::UID_LIST_TTL), function () use ($filter) {
            $query = $this->inbox()->query()->leaveUnread();

            $query = match ($filter) {
                'unread' => $query->whereUnseen(),
                'read'   => $query->whereSeen(),
                default  => $query->whereAll(),
            };

            return $query->search()->map(fn ($uid) => (int) $uid)->values()->all();
        });

        $metaByUid = $this->messageMeta($uids);

        $rows = [];

        foreach ($metaByUid as $uid => $meta) {
            foreach ($meta['attachments'] as $attachment) {
                $rows[] = [
                    'uid'                 => $uid,
                    'message_id'          => $meta['message_id'],
                    'subject'             => $meta['subject'],
                    'from'                => $meta['from'],
                    'from_name'           => $meta['from_name'],
                    'date'                => $meta['date'] ? Carbon::parse($meta['date']) : null,
                    'timestamp'           => $meta['timestamp'],
                    'seen'                => $filter === 'unread' ? false : $meta['seen'],
                    'attachment_index'    => $attachment['index'],
                    'attachment_filename' => $attachment['filename'],
                    'attachment_size'     => $attachment['size'],
                ];
            }
        }

        usort($rows, fn ($a, $b) => [$b['timestamp'], $b['uid']] <=> [$a['timestamp'], $a['uid']]);

        if ($filter === 'read') {
            $rows = array_slice($rows, 0, 50);
        }

        $total = count($rows);
        $rows  = array_slice($rows, ($page - 1) * $perPage, $perPage);

        return ['rows' => $rows, 'total' => $total];
    }

    /**
     * Drop the cached UID lists so the next listing re-runs the IMAP search
     * (after a remove, or when the user clicks Refresh).
     */
    public function forgetListing(): void
    {
        foreach (['unread', 'read', 'all'] as $filter) {
            Cache::forget($this->cacheKey("uids_{$filter}"));
        }
    }

    /**
     * @param int[] $uids
     * @return array<int, array> metadata keyed by UID, only for emails that have resume attachments
     */
    private function messageMeta(array $uids): array
    {
        if (empty($uids)) {
            return [];
        }

        $keys   = array_map(fn ($uid) => $this->cacheKey("meta_{$uid}"), $uids);
        $cached = Cache::many($keys);

        $meta    = [];
        $missing = [];

        foreach ($uids as $i => $uid) {
            if (is_array($cached[$keys[$i]] ?? null)) {
                $meta[$uid] = $cached[$keys[$i]];
            } else {
                $missing[] = $uid;
            }
        }

        foreach (array_chunk($missing, self::FETCH_CHUNK) as $chunk) {
            $fresh = [];

            try {
                $messages = $this->inbox()->query()->leaveUnread()->whereUidIn($chunk)->get();
            } catch (\Throwable $e) {
                Log::error('Resume Inbox: fetching messages failed', ['uids' => $chunk, 'message' => $e->getMessage()]);
                continue;
            }

            foreach ($messages as $message) {
                $uid = (int) $message->getUid();
                $fresh[$this->cacheKey("meta_{$uid}")] = $meta[$uid] = $this->buildMeta($message);
            }

            Cache::putMany($fresh, now()->addDays(self::META_TTL_DAYS));
        }

        return array_filter($meta, fn ($m) => !empty($m['attachments']));
    }

    private function buildMeta(Message $message): array
    {
        $fromAddress = $message->getFrom()->first();
        $date        = optional($message->getDate()->first());

        $attachments = [];
        $index       = 0;

        foreach ($message->getAttachments() as $attachment) {
            $filename  = $this->decodeMimeHeader((string) $attachment->getName());
            $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

            if (in_array($extension, self::ALLOWED_EXTENSIONS, true)) {
                $attachments[] = [
                    'index'    => $index,
                    'filename' => $filename,
                    'size'     => (int) $attachment->getSize(),
                ];
            }

            $index++;
        }

        return [
            'message_id'  => (string) $message->getMessageId(),
            'subject'     => $this->decodeMimeHeader((string) $message->getSubject()),
            'from'        => $fromAddress ? trim(($fromAddress->mailbox ?: '') . '@' . ($fromAddress->host ?: '')) : '',
            'from_name'   => $this->decodeMimeHeader((string) ($fromAddress->personal ?? '')),
            'date'        => $date?->toIso8601String(), // keeps the sender's UTC offset for display
            'timestamp'   => $date?->timestamp,
            'seen'        => $message->hasFlag('seen'),
            'attachments' => $attachments,
        ];
    }

    /**
     * Some mail clients send RFC 2047 encoded headers that the IMAP library
     * leaves as-is, e.g. "=?UTF-8?Q?Application_for_Java...?=".
     */
    private function decodeMimeHeader(string $value): string
    {
        if (!str_contains($value, '=?')) {
            return $value;
        }

        $decoded = @iconv_mime_decode($value, ICONV_MIME_DECODE_CONTINUE_ON_ERROR, 'UTF-8');

        return $decoded !== false && $decoded !== '' ? $decoded : $value;
    }

    private function cacheKey(string $suffix): string
    {
        return 'resume_inbox_v2_' . md5((string) config('imap.accounts.default.username')) . '_' . $suffix;
    }

    private function inbox(): Folder
    {
        return $this->folder ??= $this->connect()->getFolder('INBOX');
    }

    /**
     * @return array{filename: string, mime: ?string, content: string}|null
     */
    public function getAttachmentBytes(int $uid, int $attachmentIndex): ?array
    {
        try {
            $message = $this->inbox()->query()->leaveUnread()->getMessageByUid($uid);
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
            'filename'   => $this->decodeMimeHeader((string) $attachment->getName()),
            'mime'       => $attachment->getMimeType(),
            'content'    => $attachment->getContent(),
            'message_id' => (string) $message->getMessageId(),
        ];
    }

    public function markSeen(int $uid): void
    {
        try {
            $message = $this->inbox()->query()->leaveUnread()->getMessageByUid($uid);
            $message->setFlag('Seen');
        } catch (\Throwable $e) {
            Log::error('Resume Inbox: mark seen failed', ['uid' => $uid, 'message' => $e->getMessage()]);
        }
    }

    public function moveToTrash(int $uid): bool
    {
        try {
            $message = $this->inbox()->query()->leaveUnread()->getMessageByUid($uid);

            $trashPath = $this->resolveTrashFolder($this->connect());
            $message->move($trashPath);

            Cache::forget($this->cacheKey("meta_{$uid}"));
            $this->forgetListing();

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
