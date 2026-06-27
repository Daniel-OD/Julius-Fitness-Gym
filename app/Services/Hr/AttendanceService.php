<?php

namespace App\Services\Hr;

use App\Enums\AttendanceMethod;
use App\Enums\AttendanceStatus;
use App\Models\Attendance;
use App\Models\StaffProfile;
use App\Support\AppConfig;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\RateLimiter;

class AttendanceService
{
    public function __construct(
        private readonly ShiftService $shifts,
    ) {}

    public function recordScan(string $token): StaffAttendanceResult
    {
        if (! (bool) config('hr.attendance.enabled', true)) {
            return new StaffAttendanceResult('error', __('app.hr.checkin.disabled'), 503);
        }

        $profile = StaffProfile::query()->with('user')->where('attendance_token', $token)->first();

        if ($profile === null || $profile->user === null) {
            return new StaffAttendanceResult('error', __('app.hr.checkin.invalid_token'), 404);
        }

        $user = $profile->user;
        $now = Carbon::now(AppConfig::timezone());
        $today = $now->toDateString();

        $open = $this->openAttendanceQuery($user->id, $today)->first();

        if ($open !== null && $open->check_out === null) {
            return new StaffAttendanceResult(
                'already_checked_in',
                __('app.hr.checkin.already_checked_in', ['name' => $user->name]),
                422,
                $user,
                $profile,
            );
        }

        $rateLimitKey = "staff-attendance:{$user->id}";

        if (RateLimiter::tooManyAttempts($rateLimitKey, 1)) {
            $seconds = RateLimiter::availableIn($rateLimitKey);

            return new StaffAttendanceResult(
                'rate_limited',
                __('app.hr.checkin.rate_limited', ['minutes' => (int) ceil($seconds / 60)]),
                429,
                $user,
                $profile,
            );
        }

        $evaluation = $this->shifts->evaluateCheckIn($user->id, $now);
        $status = $evaluation['late'] ? AttendanceStatus::Late : AttendanceStatus::Present;

        $this->upsertAttendance($user->id, $today, [
            'check_in' => $now,
            // A fresh check-in must clear any prior check_out, otherwise re-scanning
            // after a checkout leaves check_out < check_in and corrupts worked hours.
            'check_out' => null,
            'method' => AttendanceMethod::Qr,
            'status' => $status,
            'note' => $evaluation['late']
                ? __('app.hr.checkin.late_note', ['minutes' => $evaluation['minutes_late']])
                : null,
        ]);

        RateLimiter::hit($rateLimitKey, (int) config('hr.attendance.rate_limit_minutes', 5) * 60);

        return new StaffAttendanceResult(
            'success',
            __('app.hr.checkin.success', ['name' => $user->name]),
            200,
            $user,
            $profile,
        );
    }

    public function recordCheckout(string $token): StaffAttendanceResult
    {
        $profile = StaffProfile::query()->with('user')->where('attendance_token', $token)->first();

        if ($profile === null || $profile->user === null) {
            return new StaffAttendanceResult('error', __('app.hr.checkin.invalid_token'), 404);
        }

        $user = $profile->user;
        $now = Carbon::now(AppConfig::timezone());
        $attendance = $this->openAttendanceQuery($user->id, $now->toDateString())->first();

        if ($attendance === null || $attendance->check_in === null) {
            return new StaffAttendanceResult(
                'error',
                __('app.hr.checkin.no_open_checkin'),
                404,
                $user,
                $profile,
            );
        }

        $attendance->update(['check_out' => $now]);

        return new StaffAttendanceResult(
            'checkout_success',
            __('app.hr.checkin.checkout_success', ['name' => $user->name]),
            200,
            $user,
            $profile,
        );
    }

    /**
     * @return Builder<Attendance>
     */
    public function openAttendanceQuery(int $userId, string $date): Builder
    {
        return Attendance::query()
            ->where('user_id', $userId)
            ->whereDate('date', $date);
    }

    /**
     * Mark absent for scheduled staff without an attendance record.
     */
    public function markAbsentForDate(?Carbon $date = null): int
    {
        $date ??= Carbon::yesterday(AppConfig::timezone());
        $marked = 0;

        foreach ($this->shifts->scheduledStaffForDate($date) as $user) {
            $exists = Attendance::query()
                ->where('user_id', $user->id)
                ->whereDate('date', $date->toDateString())
                ->exists();

            if ($exists) {
                continue;
            }

            $this->upsertAttendance($user->id, $date->toDateString(), [
                'method' => AttendanceMethod::Manual,
                'status' => AttendanceStatus::Absent,
                'note' => __('app.hr.attendance.auto_absent'),
            ]);

            $marked++;
        }

        return $marked;
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function upsertAttendance(int $userId, string $date, array $attributes): Attendance
    {
        $existing = Attendance::withTrashed()
            ->where('user_id', $userId)
            ->whereDate('date', $date)
            ->first();

        if ($existing !== null) {
            if ($existing->trashed()) {
                $existing->restore();
            }

            $existing->fill($attributes)->save();

            return $existing;
        }

        return Attendance::query()->create([
            'user_id' => $userId,
            'date' => $date,
            ...$attributes,
        ]);
    }
}
