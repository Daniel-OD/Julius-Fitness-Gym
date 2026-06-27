<?php

namespace App\Services\Hr;

use App\Enums\LeaveStatus;
use App\Enums\LeaveType;
use App\Enums\PayrollItemStatus;
use App\Enums\PayrollPeriodStatus;
use App\Enums\SalaryType;
use App\Models\Attendance;
use App\Models\Leave;
use App\Models\PayrollItem;
use App\Models\PayrollPeriod;
use App\Models\StaffProfile;
use App\Models\User;
use App\Support\AppConfig;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class PayrollService
{
    public function __construct(
        private readonly ShiftService $shifts,
    ) {}

    public function generatePeriod(int $month, int $year, bool $force = false): PayrollPeriod
    {
        $period = PayrollPeriod::query()->firstOrCreate(
            ['month' => $month, 'year' => $year],
            ['status' => PayrollPeriodStatus::Draft],
        );

        if (! $force && $period->status !== PayrollPeriodStatus::Draft) {
            return $period;
        }

        $period->update([
            'generated_at' => now(),
            'status' => PayrollPeriodStatus::Draft,
            'approved_by' => null,
        ]);

        $workingDays = $this->shifts->workingDaysInMonth($month, $year);
        $profiles = StaffProfile::query()->with('user')->get();

        foreach ($profiles as $profile) {
            $calculation = $this->calculateForStaff($profile, $month, $year, $workingDays);

            PayrollItem::query()->updateOrCreate(
                ['period_id' => $period->id, 'user_id' => $profile->user_id],
                [
                    ...$calculation,
                    'status' => PayrollItemStatus::Draft,
                ],
            );
        }

        PayrollItem::query()
            ->where('period_id', $period->id)
            ->whereNotIn('user_id', $profiles->pluck('user_id'))
            ->delete();

        return $period->fresh(['items.user']);
    }

    /**
     * @return array{
     *     base_salary: float,
     *     working_days: int,
     *     present_days: float,
     *     overtime_hours: float,
     *     deductions: array<int, array{label: string, amount: float}>,
     *     bonuses: array<int, array{label: string, amount: float}>,
     *     gross: float,
     *     net: float
     * }
     */
    public function calculateForStaff(StaffProfile $profile, int $month, int $year, ?int $workingDays = null): array
    {
        $workingDays ??= $this->shifts->workingDaysInMonth($month, $year);
        $start = CarbonImmutable::create($year, $month, 1, 0, 0, 0, AppConfig::timezone());
        $end = $start->endOfMonth();

        $attendances = Attendance::query()
            ->where('user_id', $profile->user_id)
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->get();

        $presentDays = round(
            $attendances->sum(fn (Attendance $row): float => $row->status->presentWeight()),
            2,
        );

        $unpaidLeaveDays = $this->approvedLeaveDays(
            $profile->user_id,
            $start,
            $end,
            LeaveType::Unpaid,
        );

        $overtimeHours = $this->calculateOvertimeHours($attendances);
        $baseSalary = (float) $profile->base_salary;
        $standardHours = (float) config('hr.attendance.standard_hours_per_day', 8);
        $overtimeMultiplier = (float) config('hr.attendance.overtime_multiplier', 1.5);

        if ($profile->salary_type === SalaryType::Hourly) {
            $totalWorkedHours = round(
                $attendances->sum(fn (Attendance $row): float => $row->workedHours()),
                2,
            );
            // Regular hours are everything that is not overtime. Clamping against
            // present_days * standard_hours instead would leave overtime hours
            // inside regular hours on mixed-length days, paying them twice.
            $regularHours = max(0, round($totalWorkedHours - $overtimeHours, 2));
            $gross = round(($regularHours * $baseSalary) + ($overtimeHours * $baseSalary * $overtimeMultiplier), 2);
        } else {
            $effectiveWorkingDays = max(1, $workingDays);
            // Unpaid leave days are paid into gross here and removed via an
            // itemized deduction below; excluding them from gross as well would
            // penalize each leave day twice.
            $paidDays = $presentDays + $unpaidLeaveDays;
            $dailyRate = $baseSalary / $effectiveWorkingDays;
            $gross = round(($dailyRate * $paidDays) + $this->overtimePay($baseSalary, $overtimeHours, $standardHours, $overtimeMultiplier), 2);
        }

        $deductions = [];
        $bonuses = [];

        if ($unpaidLeaveDays > 0 && $profile->salary_type === SalaryType::Monthly) {
            $deductions[] = [
                'label' => __('app.hr.payroll.unpaid_leave'),
                'amount' => round(($baseSalary / max(1, $workingDays)) * $unpaidLeaveDays, 2),
            ];
        }

        $deductionTotal = PayrollItem::sumAdjustments($deductions);
        $bonusTotal = PayrollItem::sumAdjustments($bonuses);
        $grossWithBonuses = round($gross + $bonusTotal, 2);
        $net = round(max(0, $grossWithBonuses - $deductionTotal), 2);

        return [
            'base_salary' => $baseSalary,
            'working_days' => $workingDays,
            'present_days' => $presentDays,
            'overtime_hours' => $overtimeHours,
            'deductions' => $deductions,
            'bonuses' => $bonuses,
            'gross' => $grossWithBonuses,
            'net' => $net,
        ];
    }

    public function approvePeriod(PayrollPeriod $period, User $approver): PayrollPeriod
    {
        $period->update([
            'status' => PayrollPeriodStatus::Approved,
            'approved_by' => $approver->id,
        ]);

        PayrollItem::query()
            ->where('period_id', $period->id)
            ->update(['status' => PayrollItemStatus::Approved]);

        return $period->fresh();
    }

    public function generatePreviousMonthDraft(): ?PayrollPeriod
    {
        $previous = Carbon::now(AppConfig::timezone())->subMonthNoOverflow();

        return $this->generatePeriod((int) $previous->month, (int) $previous->year);
    }

    /**
     * @param  Collection<int, Attendance>  $attendances
     */
    private function calculateOvertimeHours(Collection $attendances): float
    {
        $standardHours = (float) config('hr.attendance.standard_hours_per_day', 8);
        $overtime = 0.0;

        foreach ($attendances as $attendance) {
            if (! $attendance->status->countsAsPresent()) {
                continue;
            }

            $worked = $attendance->workedHours();
            $overtime += max(0, $worked - $standardHours);
        }

        return round($overtime, 2);
    }

    private function overtimePay(float $baseSalary, float $overtimeHours, float $standardHours, float $multiplier): float
    {
        if ($overtimeHours <= 0 || $standardHours <= 0) {
            return 0.0;
        }

        $hourlyEquivalent = $baseSalary / (22 * $standardHours);

        return round($overtimeHours * $hourlyEquivalent * $multiplier, 2);
    }

    private function approvedLeaveDays(int $userId, CarbonImmutable $start, CarbonImmutable $end, LeaveType $type): float
    {
        return (float) Leave::query()
            ->where('user_id', $userId)
            ->where('status', LeaveStatus::Approved)
            ->where('type', $type)
            ->whereDate('start_date', '<=', $end->toDateString())
            ->whereDate('end_date', '>=', $start->toDateString())
            ->sum('days');
    }
}
