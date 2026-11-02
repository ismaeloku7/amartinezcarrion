<?php

namespace App\Console\Commands;

use App\Services\Reminders\ReminderService;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;

class SendReminders extends Command
{
    protected $signature = 'reminders:send {--at= : Override the current UTC time (Y-m-d H:i) for testing}';

    protected $description = 'Send the push reminders that are due this minute (MDP and cards).';

    public function handle(ReminderService $service): int
    {
        $now = $this->option('at') ? CarbonImmutable::parse($this->option('at'), 'UTC') : CarbonImmutable::now('UTC');
        $sent = $service->run($now->startOfMinute());
        $this->info("Sent {$sent} reminder(s) for {$now->format('Y-m-d H:i')} UTC.");

        return self::SUCCESS;
    }
}
