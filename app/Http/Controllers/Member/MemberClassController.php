<?php

namespace App\Http\Controllers\Member;

use App\Enums\BookingStatus;
use App\Http\Controllers\Controller;
use App\Models\ClassBooking;
use App\Models\ClassSchedule;
use App\Models\Member;
use App\Services\Classes\ClassBookingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class MemberClassController extends Controller
{
    public function __construct(private readonly ClassBookingService $classBookingService) {}

    public function index(Request $request): View
    {
        $weekStart = $request->filled('week')
            ? Carbon::parse($request->input('week'))->startOfWeek(Carbon::SUNDAY)
            : Carbon::now()->startOfWeek(Carbon::SUNDAY);

        /** @var Member $member */
        $member = auth('member')->user();

        $schedule = $this->classBookingService->getWeeklySchedule($weekStart);

        $memberBookings = ClassBooking::query()
            ->where('member_id', $member->id)
            ->whereBetween('booked_date', [
                $weekStart->toDateString(),
                $weekStart->copy()->addDays(6)->toDateString(),
            ])
            ->pluck('class_schedule_id', 'booked_date')
            ->toArray();

        $prevWeek = $weekStart->copy()->subWeek()->toDateString();
        $nextWeek = $weekStart->copy()->addWeek()->toDateString();

        return view('member.classes.index', compact('schedule', 'weekStart', 'prevWeek', 'nextWeek', 'memberBookings'));
    }

    public function book(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'schedule_id' => ['required', 'integer', 'exists:class_schedules,id'],
            'date' => ['required', 'date', 'after_or_equal:today'],
        ]);

        /** @var Member $member */
        $member = auth('member')->user();
        $schedule = ClassSchedule::findOrFail($validated['schedule_id']);
        $date = Carbon::parse($validated['date']);

        try {
            $this->classBookingService->book($member, $schedule, $date);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }

        return back()->with('success', __('app.classes.notifications.booking_confirmed'));
    }

    public function cancel(ClassBooking $booking): RedirectResponse
    {
        /** @var Member $member */
        $member = auth('member')->user();

        if ($booking->member_id !== $member->id) {
            abort(403);
        }

        try {
            $this->classBookingService->cancel($booking);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        return back()->with('success', __('app.classes.notifications.booking_cancelled'));
    }

    public function myBookings(): View
    {
        /** @var Member $member */
        $member = auth('member')->user();

        $bookings = ClassBooking::query()
            ->with(['schedule.gymClass.instructor'])
            ->where('member_id', $member->id)
            ->whereDate('booked_date', '>=', today())
            ->whereNot('status', BookingStatus::Cancelled)
            ->orderBy('booked_date')
            ->get();

        return view('member.classes.my-bookings', compact('bookings'));
    }
}
