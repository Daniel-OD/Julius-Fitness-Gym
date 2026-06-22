<?php

namespace App\Services\Classes;

use App\Enums\BookingStatus;
use App\Models\ClassBooking;
use App\Models\ClassSchedule;
use App\Models\Member;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ClassBookingService
{
    /**
     * Book a member into a class schedule on a specific date.
     *
     * @throws ValidationException
     */
    public function book(Member $member, ClassSchedule $schedule, Carbon $date): ClassBooking
    {
        return DB::transaction(function () use ($member, $schedule, $date): ClassBooking {
            $gymClass = $schedule->gymClass()->lockForUpdate()->first();

            if (! $gymClass || ! $gymClass->is_active) {
                throw ValidationException::withMessages([
                    'schedule' => [__('app.classes.errors.class_inactive')],
                ]);
            }

            if (! $schedule->is_active) {
                throw ValidationException::withMessages([
                    'schedule' => [__('app.classes.errors.schedule_inactive')],
                ]);
            }

            if ($schedule->day_of_week !== $date->dayOfWeek) {
                throw ValidationException::withMessages([
                    'date' => [__('app.classes.errors.date_mismatch')],
                ]);
            }

            $alreadyBooked = ClassBooking::query()
                ->where('member_id', $member->id)
                ->where('class_schedule_id', $schedule->id)
                ->whereDate('booked_date', $date)
                ->whereNot('status', BookingStatus::Cancelled)
                ->exists();

            if ($alreadyBooked) {
                throw ValidationException::withMessages([
                    'schedule' => [__('app.classes.errors.already_booked')],
                ]);
            }

            $booked = ClassBooking::query()
                ->where('class_schedule_id', $schedule->id)
                ->whereDate('booked_date', $date)
                ->whereNot('status', BookingStatus::Cancelled)
                ->count();

            if ($booked >= $gymClass->capacity) {
                throw ValidationException::withMessages([
                    'schedule' => [__('app.classes.errors.class_full')],
                ]);
            }

            return ClassBooking::query()->create([
                'member_id' => $member->id,
                'class_schedule_id' => $schedule->id,
                'booked_date' => $date->toDateString(),
                'status' => BookingStatus::Booked,
            ]);
        });
    }

    /**
     * Cancel a booking. Members can only cancel upcoming bookings.
     *
     * @throws ValidationException
     */
    public function cancel(ClassBooking $booking, bool $allowPast = false): void
    {
        if (! $allowPast && $booking->booked_date->isPast()) {
            throw ValidationException::withMessages([
                'booking' => [__('app.classes.errors.cannot_cancel_past')],
            ]);
        }

        if ($booking->status === BookingStatus::Cancelled) {
            throw ValidationException::withMessages([
                'booking' => [__('app.classes.errors.already_cancelled')],
            ]);
        }

        $booking->update(['status' => BookingStatus::Cancelled]);
    }

    /**
     * Get the weekly schedule grouped by day_of_week (0-6), enriched with available slot counts.
     *
     * @return Collection<int, mixed>
     */
    public function getWeeklySchedule(Carbon $weekStart): Collection
    {
        $weekEnd = $weekStart->copy()->endOfWeek(Carbon::SATURDAY);

        $schedules = ClassSchedule::query()
            ->with(['gymClass.instructor'])
            ->where('is_active', true)
            ->whereHas('gymClass', fn ($q) => $q->where('is_active', true))
            ->get();

        return collect(range(0, 6))->mapWithKeys(function (int $day) use ($schedules, $weekStart): array {
            $date = $weekStart->copy()->startOfWeek(Carbon::SUNDAY)->addDays($day);
            if ($date->lt($weekStart)) {
                $date->addWeek();
            }

            $daySchedules = $schedules->where('day_of_week', $day)->map(function (ClassSchedule $schedule) use ($date): array {
                return [
                    'schedule' => $schedule,
                    'date' => $date->copy(),
                    'available_slots' => $this->getAvailableSlots($schedule, $date),
                ];
            })->values();

            return [$day => $daySchedules];
        });
    }

    /**
     * Return number of available slots for a schedule on a given date.
     */
    public function getAvailableSlots(ClassSchedule $schedule, Carbon $date): int
    {
        $gymClass = $schedule->gymClass;

        if (! $gymClass) {
            return 0;
        }

        $booked = ClassBooking::query()
            ->where('class_schedule_id', $schedule->id)
            ->whereDate('booked_date', $date)
            ->whereNot('status', BookingStatus::Cancelled)
            ->count();

        return max(0, $gymClass->capacity - $booked);
    }
}
