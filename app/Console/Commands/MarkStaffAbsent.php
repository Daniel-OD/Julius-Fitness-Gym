<?php

namespace App\Console\Commands;

use App\Services\Hr\AttendanceService;
use App\Support\AppConfig;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

#[Description('Mark absent staff who had a shift but no attendance record')]
#[Signature('gym:staff-attendance {--date= : Date to process (Y-m-d), defaults to yesterday}')]
class MarkStaffAbsent extends Command
{
    public function handle(AttendanceService $attendance): int
    {
        $dateInput = $this->option('date');
        $date = filled($dateInput)
            ? Carbon::parse((string) $dateInput, AppConfig::timezone())
            : Carbon::yesterday(AppConfig::timezone());

        $marked = $attendance->markAbsentForDate($date);

        $this->info("{$marked} staff member(s) marked absent for {$date->toDateString()}.");

        return self::SUCCESS;
    }
}
