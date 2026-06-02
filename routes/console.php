<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('gymie:invoices --mark-overdue')->dailyAt('00:05');
Schedule::command('gymie:subscriptions')->dailyAt('00:10');
Schedule::command('gymie:subscription-expiry-notifications')->dailyAt('09:00');
