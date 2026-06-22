<?php

namespace App\Http\Controllers;

use App\Enums\BookingStatus;
use App\Enums\Status;
use App\Models\ClassBooking;
use App\Models\ClassSchedule;
use App\Models\Plan;
use App\Models\Service;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $plans = Plan::query()
            ->where('status', Status::Active)
            ->orderBy('amount')
            ->get();

        $highlightPlanId = null;

        if ($plans->count() >= 2) {
            $highlightPlanId = $plans->get((int) floor($plans->count() / 2))?->id;
        }

        $services = Service::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $weekStart = Carbon::now()->startOfWeek(Carbon::SUNDAY);
        $weekEnd = $weekStart->copy()->addDays(6);

        $gymSchedule = ClassSchedule::query()
            ->with(['gymClass.instructor'])
            ->where('is_active', true)
            ->whereHas('gymClass', fn ($q) => $q->where('is_active', true))
            ->get()
            ->groupBy('day_of_week')
            ->map(fn ($schedules) => $schedules->map(fn (ClassSchedule $s) => [
                'name' => $s->gymClass->name,
                'color' => $s->gymClass->color,
                'instructor' => $s->gymClass->instructor?->name,
                'start_time' => substr($s->start_time, 0, 5),
                'location' => $s->location,
                'capacity' => $s->gymClass->capacity,
                'available' => $s->gymClass->capacity - ClassBooking::query()
                    ->where('class_schedule_id', $s->id)
                    ->whereBetween('booked_date', [$weekStart->toDateString(), $weekEnd->toDateString()])
                    ->whereNot('status', BookingStatus::Cancelled)
                    ->count(),
            ])->values());

        return view('home', [
            'plans' => $plans,
            'highlightPlanId' => $highlightPlanId,
            'services' => $services,
            'gymSchedule' => $gymSchedule,
        ]);
    }
}
