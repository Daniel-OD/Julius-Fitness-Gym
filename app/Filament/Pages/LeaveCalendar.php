<?php

namespace App\Filament\Pages;

use App\Enums\LeaveStatus;
use App\Models\Leave;
use App\Support\AppConfig;
use Filament\Pages\Page;
use Illuminate\Support\Carbon;

class LeaveCalendar extends Page
{
    protected static ?string $slug = 'leave-calendar';

    protected static ?int $navigationSort = 55;

    protected string $view = 'filament.pages.leave-calendar';

    public int $month;

    public int $year;

    #[\Override]
    public static function getNavigationLabel(): string
    {
        return __('app.hr.leave_calendar');
    }

    #[\Override]
    public static function getNavigationGroup(): ?string
    {
        return __('app.navigation.groups.hr');
    }

    #[\Override]
    public function getTitle(): string
    {
        return static::getNavigationLabel();
    }

    public function mount(): void
    {
        $now = Carbon::now(AppConfig::timezone());
        $this->month = (int) $now->month;
        $this->year = (int) $now->year;
    }

    public function previousMonth(): void
    {
        $date = Carbon::create($this->year, $this->month, 1)->subMonth();
        $this->month = (int) $date->month;
        $this->year = (int) $date->year;
    }

    public function nextMonth(): void
    {
        $date = Carbon::create($this->year, $this->month, 1)->addMonth();
        $this->month = (int) $date->month;
        $this->year = (int) $date->year;
    }

    /**
     * @return array<int, array{day: int|null, leaves: array<int, Leave>}>
     */
    public function calendarWeeks(): array
    {
        $start = Carbon::create($this->year, $this->month, 1, 0, 0, 0, AppConfig::timezone());
        $end = $start->copy()->endOfMonth();
        $cursor = $start->copy()->startOfWeek(Carbon::MONDAY);
        $weeks = [];

        $leaves = Leave::query()
            ->with('user')
            ->where('status', LeaveStatus::Approved)
            ->whereDate('start_date', '<=', $end->toDateString())
            ->whereDate('end_date', '>=', $start->toDateString())
            ->get();

        while ($cursor->lte($end->copy()->endOfWeek(Carbon::MONDAY))) {
            $week = [];

            for ($i = 0; $i < 7; $i++) {
                $day = $cursor->copy();
                $week[] = [
                    'day' => $day->month === $this->month ? (int) $day->day : null,
                    'date' => $day->toDateString(),
                    'leaves' => $leaves->filter(fn (Leave $leave): bool => $leave->coversDate($day))->values()->all(),
                    'in_month' => $day->month === $this->month,
                ];
                $cursor->addDay();
            }

            $weeks[] = $week;
        }

        return $weeks;
    }

    public function monthLabel(): string
    {
        return __('app.hr.months.'.$this->month).' '.$this->year;
    }
}
