<?php

namespace App\Providers;

use App\Services\ResumeInboxService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // One instance per request so the IMAP connection is reused between
        // a Livewire action and the render that follows it.
        $this->app->scoped(ResumeInboxService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
