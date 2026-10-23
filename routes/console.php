<?php

use Illuminate\Foundation\Inspiring;
use App\Services\ResumeInboxService;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Pre-fetches metadata of newly arrived inbox emails so the admin Resume
// Inbox page never has to download emails itself (first load of a big
// inbox takes minutes; cached loads take well under a second).
Artisan::command('resume-inbox:warm', function (ResumeInboxService $inbox) {
    $inbox->forgetListing();
    $result = $inbox->listAttachmentRows('unread', 1, 1);
    $this->info("Resume inbox cache warmed: {$result['total']} unread resume attachment(s).");
})->purpose('Pre-cache Resume Inbox email metadata');

Schedule::command('resume-inbox:warm')->everyFiveMinutes()->withoutOverlapping();
