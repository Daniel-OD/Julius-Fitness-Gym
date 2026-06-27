<?php

use App\Enums\AttendanceStatus;
use App\Enums\LeaveStatus;
use App\Enums\LeaveType;
use App\Enums\PayrollPeriodStatus;
use App\Enums\SalaryType;
use App\Models\Attendance;
use App\Models\Leave;
use App\Models\StaffProfile;
use App\Models\User;
use App\Services\Hr\PayrollService;
use App\Support\AppConfig;
use Illuminate\Support\Carbon;

beforeEach(function (): void {
    Carbon::setTestNow(Carbon::create(2026, 6, 15, 9, 0, 0, AppConfig::timezone()));
});

afterEach(function (): void {
    Carbon::setTestNow();
});

it('calculates monthly payroll pro-rata from attendance', function (): void {
    $user = User::factory()->create();
    $profile = StaffProfile::factory()->for($user)->create([
        'base_salary' => 3000,
        'salary_type' => SalaryType::Monthly,
    ]);

    Attendance::factory()->for($user)->create([
        'date' => '2026-06-02',
        'status' => AttendanceStatus::Present,
    ]);
    Attendance::factory()->for($user)->create([
        'date' => '2026-06-03',
        'status' => AttendanceStatus::Present,
    ]);
    Attendance::factory()->for($user)->create([
        'date' => '2026-06-04',
        'status' => AttendanceStatus::HalfDay,
    ]);

    $service = app(PayrollService::class);
    $workingDays = 22;
    $result = $service->calculateForStaff($profile, 6, 2026, $workingDays);

    expect($result['present_days'])->toBe(2.5)
        ->and($result['working_days'])->toBe(22)
        ->and($result['gross'])->toBe(round(3000 * (2.5 / 22), 2))
        ->and($result['net'])->toBe($result['gross']);
});

it('deducts unpaid leave from monthly payroll', function (): void {
    $user = User::factory()->create();
    $profile = StaffProfile::factory()->for($user)->create([
        'base_salary' => 2200,
        'salary_type' => SalaryType::Monthly,
    ]);

    foreach (range(1, 20) as $day) {
        Attendance::factory()->for($user)->create([
            'date' => sprintf('2026-06-%02d', $day),
            'status' => AttendanceStatus::Present,
        ]);
    }

    Leave::factory()->for($user)->create([
        'type' => LeaveType::Unpaid,
        'start_date' => '2026-06-21',
        'end_date' => '2026-06-22',
        'days' => 2,
        'status' => LeaveStatus::Approved,
    ]);

    $service = app(PayrollService::class);
    $result = $service->calculateForStaff($profile, 6, 2026, 22);

    // The 2 unpaid-leave days are paid into gross and then itemized as a single
    // deduction, so each non-worked day is penalized exactly once: net reflects
    // the 20 present days (20/22), not 18/22.
    expect($result['deductions'])->not->toBeEmpty()
        ->and($result['gross'])->toBe(round(2200 * (22 / 22), 2))
        ->and($result['net'])->toBe(round(2200 * (20 / 22), 2));
});

it('calculates hourly payroll from worked hours', function (): void {
    $user = User::factory()->create();
    $profile = StaffProfile::factory()->for($user)->create([
        'base_salary' => 25,
        'salary_type' => SalaryType::Hourly,
    ]);

    Attendance::factory()->for($user)->create([
        'date' => '2026-06-02',
        'check_in' => '2026-06-02 09:00:00',
        'check_out' => '2026-06-02 17:00:00',
        'status' => AttendanceStatus::Present,
    ]);

    $service = app(PayrollService::class);
    $result = $service->calculateForStaff($profile, 6, 2026, 22);

    expect($result['gross'])->toBe(200.0)
        ->and($result['net'])->toBe(200.0);
});

it('generates payroll period with items for all staff profiles', function (): void {
    StaffProfile::factory()->count(2)->create();

    $period = app(PayrollService::class)->generatePeriod(6, 2026, force: true);

    expect($period->month)->toBe(6)
        ->and($period->year)->toBe(2026)
        ->and($period->status)->toBe(PayrollPeriodStatus::Draft)
        ->and($period->items)->toHaveCount(2);
});

it('approves payroll period and items', function (): void {
    $admin = User::factory()->create();
    $period = app(PayrollService::class)->generatePeriod(5, 2026, force: true);

    app(PayrollService::class)->approvePeriod($period, $admin);

    $period->refresh();

    expect($period->status)->toBe(PayrollPeriodStatus::Approved)
        ->and($period->approved_by)->toBe($admin->id)
        ->and($period->items->every(fn ($item) => $item->status->value === 'approved'))->toBeTrue();
});

it('calculates hourly payroll with overtime hours', function (): void {
    $user = User::factory()->create();
    $profile = StaffProfile::factory()->for($user)->create([
        'base_salary' => 20,
        'salary_type' => SalaryType::Hourly,
    ]);

    // 10 hours worked on a single day (2 hours overtime beyond 8-hour standard)
    Attendance::factory()->for($user)->create([
        'date' => '2026-06-02',
        'check_in' => '2026-06-02 08:00:00',
        'check_out' => '2026-06-02 18:00:00',
        'status' => AttendanceStatus::Present,
    ]);

    $service = app(PayrollService::class);
    $result = $service->calculateForStaff($profile, 6, 2026, 22);

    // Regular 8h at $20 = $160, overtime 2h at $20 * 1.5 = $60, total $220
    expect($result['overtime_hours'])->toBe(2.0)
        ->and($result['gross'])->toBe(220.0)
        ->and($result['net'])->toBe(220.0);
});

it('does not double-count overtime hours across mixed-length days', function (): void {
    $user = User::factory()->create();
    $profile = StaffProfile::factory()->for($user)->create([
        'base_salary' => 20,
        'salary_type' => SalaryType::Hourly,
    ]);

    // Day 1: 10h worked (2h overtime beyond the 8h standard).
    Attendance::factory()->for($user)->create([
        'date' => '2026-06-02',
        'check_in' => '2026-06-02 08:00:00',
        'check_out' => '2026-06-02 18:00:00',
        'status' => AttendanceStatus::Present,
    ]);
    // Day 2: 4h worked (no overtime).
    Attendance::factory()->for($user)->create([
        'date' => '2026-06-03',
        'check_in' => '2026-06-03 09:00:00',
        'check_out' => '2026-06-03 13:00:00',
        'status' => AttendanceStatus::Present,
    ]);

    $result = app(PayrollService::class)->calculateForStaff($profile, 6, 2026, 22);

    // 14h worked, 2h overtime → 12h regular. 12*20 + 2*20*1.5 = 240 + 60 = 300.
    expect($result['overtime_hours'])->toBe(2.0)
        ->and($result['gross'])->toBe(300.0)
        ->and($result['net'])->toBe(300.0);
});

it('clamps monthly payroll working days to minimum of 1 when zero passed', function (): void {
    $user = User::factory()->create();
    $profile = StaffProfile::factory()->for($user)->create([
        'base_salary' => 3000,
        'salary_type' => SalaryType::Monthly,
    ]);

    Attendance::factory()->for($user)->create([
        'date' => '2026-06-02',
        'status' => AttendanceStatus::Present,
    ]);

    $service = app(PayrollService::class);
    // Passing 0 working days should not cause division by zero
    $result = $service->calculateForStaff($profile, 6, 2026, workingDays: 0);

    expect($result['working_days'])->toBe(0)
        ->and($result['gross'])->toBeGreaterThan(0.0);
});

it('does not regenerate period when force is false and status is approved', function (): void {
    $admin = User::factory()->create();
    $service = app(PayrollService::class);

    $period = $service->generatePeriod(6, 2026, force: true);
    $service->approvePeriod($period, $admin);

    $approvedAt = $period->fresh()->generated_at;

    // Second call without force should return the same approved period unchanged
    $returned = $service->generatePeriod(6, 2026, force: false);

    expect($returned->status)->toBe(PayrollPeriodStatus::Approved)
        ->and($returned->generated_at->toDateTimeString())->toBe($approvedAt->toDateTimeString());
});

it('skips unpaid leave deduction for hourly employees', function (): void {
    $user = User::factory()->create();
    $profile = StaffProfile::factory()->for($user)->create([
        'base_salary' => 25,
        'salary_type' => SalaryType::Hourly,
    ]);

    Attendance::factory()->for($user)->create([
        'date' => '2026-06-02',
        'check_in' => '2026-06-02 09:00:00',
        'check_out' => '2026-06-02 17:00:00',
        'status' => AttendanceStatus::Present,
    ]);

    Leave::factory()->for($user)->create([
        'type' => LeaveType::Unpaid,
        'start_date' => '2026-06-21',
        'end_date' => '2026-06-22',
        'days' => 2,
        'status' => LeaveStatus::Approved,
    ]);

    $result = app(PayrollService::class)->calculateForStaff($profile, 6, 2026, 22);

    // Hourly employees are NOT subject to unpaid leave deductions
    expect($result['deductions'])->toBeEmpty();
});
