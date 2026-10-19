<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ResumeInboxService;

class ResumeInboxAttachmentController extends Controller
{
    public function stream(int $uid, int $index, ResumeInboxService $inbox)
    {
        $attachment = $inbox->getAttachmentBytes($uid, $index);

        abort_if(!$attachment, 404, 'Attachment not found.');

        // $inbox->markSeen($uid);

        $extension = strtolower(pathinfo($attachment['filename'], PATHINFO_EXTENSION));
        $disposition = $extension === 'pdf' ? 'inline' : 'attachment';

        return response($attachment['content'])
            ->header('Content-Type', $attachment['mime'] ?: 'application/octet-stream')
            ->header('Content-Disposition', $disposition . '; filename="' . $attachment['filename'] . '"');
    }
}
