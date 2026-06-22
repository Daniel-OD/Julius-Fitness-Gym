<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\ClassBooking;
use App\Models\ClassSchedule;
use App\Models\GymClass;
use App\Models\Member;
use App\Services\Classes\ClassBookingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class GymClassesController extends ApiController
{
    public function __construct(private readonly ClassBookingService $classBookingService) {}

    /**
     * GET /api/v1/classes — active classes with schedules.
     */
    public function index(Request $request): JsonResponse
    {
        $this->requirePermission($request, 'ViewAny:GymClass');

        $classes = GymClass::query()
            ->with(['instructor:id,name', 'schedules' => fn ($q) => $q->where('is_active', true)])
            ->where('is_active', true)
            ->get();

        return response()->json(['data' => $classes]);
    }

    /**
     * GET /api/v1/classes/schedule?week=2026-06-22 — weekly schedule.
     */
    public function schedule(Request $request): JsonResponse
    {
        $weekStart = $request->filled('week')
            ? Carbon::parse($request->input('week'))->startOfWeek(Carbon::SUNDAY)
            : Carbon::now()->startOfWeek(Carbon::SUNDAY);

        $schedule = $this->classBookingService->getWeeklySchedule($weekStart);

        $data = $schedule->map(fn ($slots) => $slots->map(fn (array $slot): array => [
            'schedule_id' => $slot['schedule']->id,
            'gym_class' => $slot['schedule']->gymClass->name,
            'instructor' => $slot['schedule']->gymClass->instructor?->name,
            'start_time' => $slot['schedule']->start_time,
            'location' => $slot['schedule']->location,
            'color' => $slot['schedule']->gymClass->color,
            'date' => $slot['date']->toDateString(),
            'available_slots' => $slot['available_slots'],
        ]));

        return response()->json([
            'week_start' => $weekStart->toDateString(),
            'data' => $data,
        ]);
    }

    /**
     * POST /api/v1/bookings — book a class.
     */
    public function book(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'schedule_id' => ['required', 'integer', 'exists:class_schedules,id'],
            'date' => ['required', 'date', 'after_or_equal:today'],
        ]);

        $member = $this->currentUser($request);
        $memberModel = Member::where('user_id', $member->getAuthIdentifier())->firstOrFail();
        $schedule = ClassSchedule::findOrFail($validated['schedule_id']);
        $date = Carbon::parse($validated['date']);

        $booking = $this->classBookingService->book($memberModel, $schedule, $date);

        return response()->json(['data' => $booking->load('schedule.gymClass')], 201);
    }

    /**
     * GET /api/v1/bookings — member's bookings.
     */
    public function myBookings(Request $request): JsonResponse
    {
        $member = $this->currentUser($request);
        $memberModel = Member::where('user_id', $member->getAuthIdentifier())->firstOrFail();

        $bookings = ClassBooking::query()
            ->with(['schedule.gymClass.instructor'])
            ->where('member_id', $memberModel->id)
            ->whereDate('booked_date', '>=', today())
            ->orderBy('booked_date')
            ->get();

        return response()->json(['data' => $bookings]);
    }

    /**
     * DELETE /api/v1/bookings/{id} — cancel a booking.
     */
    public function cancelBooking(Request $request, ClassBooking $booking): JsonResponse
    {
        $member = $this->currentUser($request);
        $memberModel = Member::where('user_id', $member->getAuthIdentifier())->firstOrFail();

        if ($booking->member_id !== $memberModel->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $this->classBookingService->cancel($booking);

        return response()->json(['message' => 'Booking cancelled'], 200);
    }
}
