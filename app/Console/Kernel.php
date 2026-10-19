<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // -------------------------------------------------------
        // Queue Monitor — restart worker daily to prevent memory leaks
        // -------------------------------------------------------
        $schedule->command('queue:restart')->daily();

        // -------------------------------------------------------
        // Clear expired password reset tokens — daily
        // -------------------------------------------------------
        $schedule->command('auth:clear-resets')->daily();

        // -------------------------------------------------------
        // Clear old telescope entries (if using Laravel Telescope)
        // -------------------------------------------------------
        // $schedule->command('telescope:prune')->daily();

        // -------------------------------------------------------
        // Clear application cache — weekly
        // -------------------------------------------------------
        // $schedule->command('cache:clear')->weekly();

        // -------------------------------------------------------
        // Send scheduled/reminder emails — hourly
        // -------------------------------------------------------
        // $schedule->command('emails:send-reminders')->hourly();

        // -------------------------------------------------------
        // Expire old job listings automatically
        // -------------------------------------------------------
        // $schedule->command('jobs:expire')->daily();

        // -------------------------------------------------------
        // Generate sitemap — daily at midnight
        // -------------------------------------------------------
        // $schedule->command('sitemap:generate')->dailyAt('00:00');

        // -------------------------------------------------------
        // Clean up old failed queue jobs — weekly
        // -------------------------------------------------------
        $schedule->command('queue:flush')->weekly();

        // -------------------------------------------------------
        // Database backup (if using spatie/laravel-backup)
        // -------------------------------------------------------
        // $schedule->command('backup:run')->dailyAt('01:00');
        // $schedule->command('backup:clean')->dailyAt('01:30');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}