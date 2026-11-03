<?php

use App\Models\ReminderLog;
use Illuminate\Support\Facades\Schedule;

Schedule::command('reminders:send')->everyMinute()->withoutOverlapping(10);

// The reminder log only needs to remember today's sends.
Schedule::call(fn () => ReminderLog::query()->where('local_date', '<', now()->subDays(7)->toDateString())->delete())
    ->daily()->name('reminders:prune');
