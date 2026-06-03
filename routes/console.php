<?php

use App\Helpers\Helpers;
use Illuminate\Support\Facades\Schedule;

Schedule::command('gymie:invoices --mark-overdue')->dailyAt('00:05');
Schedule::command('gymie:subscriptions')->dailyAt('00:10');
Schedule::command('gymie:subscription-expiry-notifications')->dailyAt('09:00');

// Daily backup — runs only when backup is enabled and trigger includes end-of-day
(function (): void {
    $backup = is_array($s = Helpers::getSettings()['backup'] ?? null) ? $s : [];

    if (empty($backup['enabled'])) {
        return;
    }

    if (! in_array($backup['trigger'] ?? '', ['end_of_day', 'both'], true)) {
        return;
    }

    $time = preg_match('/^\d{2}:\d{2}$/', (string) ($backup['end_of_day_time'] ?? ''))
        ? $backup['end_of_day_time']
        : '22:00';

    Schedule::command('app:backup', ['--trigger' => 'end_of_day'])->dailyAt($time);
})();
