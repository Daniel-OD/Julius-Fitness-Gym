<?php

namespace App\Services\Hr;

use App\Models\Shift;
use App\Models\ShiftAssignment;
use App\Models\User;
use App\Support\AppConfig;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class ShiftService
{
    /**
     * @return Collection<int, ShiftAssignment>
     */
    public function assignmentsForUser(int $userId, ?Carbon $date = null): Collection
    {
        $date ??= Carbon::now(AppConfig::timezone());

        return ShiftAssignment::query()
            ->with('shift')
            ->where('user_id', $userId)
            ->whereDate('valid_from', '<=', $date)
            ->where(function ($query) use ($date): void {
                $query
                    ->whereNull('valid_until')
                    ->orWhereDate('valid_until', '>=', $date);
            })
            ->get();
    }

    public function currentShiftForUser(int $userId, ?Carbon $date = null): ?Shift
    {
        $date ??= Carbon::now(AppConfig::timezone());
        $dayOfWeek = $date->dayOfWeek;

        return $this->assignmentsForUser($userId, $date)
            ->map(fn (ShiftAssignment $assignment): ?Shift => $assignment->shift)
            ->filter(fn (?Shift $shift): bool => $shift !== null && $shift->is_active && $shift->appliesOnDay($dayOfWeek))
            ->sortBy(fn (Shift $shift): string => (string) $shift->start_time)
            ->first();
    }

    /**
     * @return array{late: bool, minutes_late: int, shift: Shift|null}
     */
    public function evaluateCheckIn(int $userId, Carbon $checkIn): array
    {
        $shift = $this->currentShiftForUser($userId, $checkIn);
        $graceMinutes = (int) config('hr.attendance.grace_period_minutes', 15);

        if ($shift === null) {
            return ['late' => false, 'minutes_late' => 0, 'shift' => null];
        }

        $scheduledStart = Carbon::parse(
            $checkIn->toDateString().' '.$shift->start_time,
            AppConfig::timezone(),
        );
        $graceEnd = $scheduledStart->copy()->addMinutes($graceMinutes);

        if ($checkIn->lte($graceEnd)) {
            return ['late' => false, 'minutes_late' => 0, 'shift' => $shift];
        }

        return [
            'late' => true,
            'minutes_late' => (int) $graceEnd->diffInMinutes($checkIn),
            'shift' => $shift,
        ];
    }

    /**
     * Detect whether checkout happened before shift end.
     */
    public function isEarlyCheckout(int $userId, Carbon $checkOut): bool
    {
        $shift = $this->currentShiftForUser($userId, $checkOut);

        if ($shift === null) {
            return false;
        }

        $scheduledEnd = Carbon::parse(
            $checkOut->toDateString().' '.$shift->end_time,
            AppConfig::timezone(),
        );

        return $checkOut->lt($scheduledEnd);
    }

    /**
     * Count weekdays (Mon–Fri) in a month.
     */
    public function workingDaysInMonth(int $month, int $year): int
    {
        $start = CarbonImmutable::create($year, $month, 1, 0, 0, 0, AppConfig::timezone());
        $end = $start->endOfMonth();
        $days = 0;

        for ($date = $start; $date->lte($end); $date = $date->addDay()) {
            if ($date->isWeekday()) {
                $days++;
            }
        }

        return $days;
    }

    /**
     * Staff scheduled to work on a given date (has an active shift assignment).
     *
     * @return Collection<int, User>
     */
    public function scheduledStaffForDate(Carbon $date): Collection
    {
        $dayOfWeek = $date->dayOfWeek;

        return ShiftAssignment::query()
            ->with(['user.staffProfile', 'shift'])
            ->whereDate('valid_from', '<=', $date)
            ->where(function ($query) use ($date): void {
                $query
                    ->whereNull('valid_until')
                    ->orWhereDate('valid_until', '>=', $date);
            })
            ->get()
            ->filter(function (ShiftAssignment $assignment) use ($dayOfWeek): bool {
                $shift = $assignment->shift;

                return $shift !== null
                    && $shift->is_active
                    && $shift->appliesOnDay($dayOfWeek);
            })
            ->map(fn (ShiftAssignment $assignment): ?User => $assignment->user)
            ->filter()
            ->unique('id')
            ->values();
    }
}
