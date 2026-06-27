<?php

use App\Models\Attendance;
use App\Models\StaffProfile;
use App\Services\Hr\AttendanceService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    config(['app.timezone' => 'Europe/Bucharest']);
    Carbon::setTestNow(Carbon::parse('2026-06-02 09:00:00', 'Europe/Bucharest'));
});

afterEach(function (): void {
    Carbon::setTestNow();
});

it('clears the prior check_out when staff re-checks in the same day', function (): void {
    $profile = StaffProfile::factory()->create();
    $token = $profile->attendance_token;
    $service = app(AttendanceService::class);

    // Check in, then check out at 13:00.
    $service->recordScan($token);
    Carbon::setTestNow(Carbon::parse('2026-06-02 13:00:00', 'Europe/Bucharest'));
    $service->recordCheckout($token);

    $attendance = Attendance::where('user_id', $profile->user_id)->firstOrFail();
    expect($attendance->check_out)->not->toBeNull();

    // Re-scan at 15:00 (a fresh check-in for an evening shift).
    Carbon::setTestNow(Carbon::parse('2026-06-02 15:00:00', 'Europe/Bucharest'));
    RateLimiter::clear("staff-attendance:{$profile->user_id}");
    $service->recordScan($token);

    $attendance->refresh();

    // check_out must be cleared so worked hours never go negative.
    expect($attendance->check_out)->toBeNull()
        ->and($attendance->check_in->format('H:i'))->toBe('15:00')
        ->and($attendance->workedHours())->toBe(0.0);
});
