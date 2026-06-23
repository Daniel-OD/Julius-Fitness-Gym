<?php

use App\Enums\BookingStatus;
use App\Models\ClassBooking;
use App\Models\ClassSchedule;
use App\Models\GymClass;
use App\Models\Member;
use App\Services\Classes\ClassBookingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->service = app(ClassBookingService::class);
});

it('books a member into an available class slot', function (): void {
    $member = Member::factory()->create();
    $gymClass = GymClass::factory()->create(['capacity' => 10, 'is_active' => true]);
    $monday = Carbon::now()->next(Carbon::MONDAY);
    $schedule = ClassSchedule::factory()->create([
        'gym_class_id' => $gymClass->id,
        'day_of_week' => $monday->dayOfWeek,
        'is_active' => true,
    ]);

    $booking = $this->service->book($member, $schedule, $monday);

    expect($booking)->toBeInstanceOf(ClassBooking::class)
        ->and($booking->status)->toBe(BookingStatus::Booked)
        ->and($booking->member_id)->toBe($member->id);
});

it('cannot book when class is full', function (): void {
    $member = Member::factory()->create();
    $gymClass = GymClass::factory()->create(['capacity' => 2, 'is_active' => true]);
    $monday = Carbon::now()->next(Carbon::MONDAY);
    $schedule = ClassSchedule::factory()->create([
        'gym_class_id' => $gymClass->id,
        'day_of_week' => $monday->dayOfWeek,
        'is_active' => true,
    ]);

    // Fill the class to capacity
    ClassBooking::factory()->count(2)->create([
        'class_schedule_id' => $schedule->id,
        'booked_date' => $monday->toDateString(),
        'status' => BookingStatus::Booked,
    ]);

    expect(fn () => $this->service->book($member, $schedule, $monday))
        ->toThrow(ValidationException::class);
});

it('cannot double-book the same member for the same slot', function (): void {
    $member = Member::factory()->create();
    $gymClass = GymClass::factory()->create(['capacity' => 10, 'is_active' => true]);
    $monday = Carbon::now()->next(Carbon::MONDAY);
    $schedule = ClassSchedule::factory()->create([
        'gym_class_id' => $gymClass->id,
        'day_of_week' => $monday->dayOfWeek,
        'is_active' => true,
    ]);

    $this->service->book($member, $schedule, $monday);

    expect(fn () => $this->service->book($member, $schedule, $monday))
        ->toThrow(ValidationException::class);
});

it('cancel booking frees up a slot', function (): void {
    $gymClass = GymClass::factory()->create(['capacity' => 1, 'is_active' => true]);
    $monday = Carbon::now()->next(Carbon::MONDAY);
    $schedule = ClassSchedule::factory()->create([
        'gym_class_id' => $gymClass->id,
        'day_of_week' => $monday->dayOfWeek,
        'is_active' => true,
    ]);

    $member1 = Member::factory()->create();
    $member2 = Member::factory()->create();

    $booking = $this->service->book($member1, $schedule, $monday);

    // Slot is now full
    expect(fn () => $this->service->book($member2, $schedule, $monday))
        ->toThrow(ValidationException::class);

    $this->service->cancel($booking);

    // Now member2 can book
    $booking2 = $this->service->book($member2, $schedule, $monday);
    expect($booking2)->toBeInstanceOf(ClassBooking::class);
});

it('weekly schedule returns correct days', function (): void {
    $gymClass = GymClass::factory()->create(['is_active' => true]);
    $monday = Carbon::now()->startOfWeek(Carbon::SUNDAY);
    $schedule = ClassSchedule::factory()->create([
        'gym_class_id' => $gymClass->id,
        'day_of_week' => 1, // Monday
        'is_active' => true,
    ]);

    $weekSchedule = $this->service->getWeeklySchedule($monday);

    expect($weekSchedule)->toHaveCount(7)
        ->and($weekSchedule->get(1))->not->toBeEmpty()
        ->and($weekSchedule->get(0))->toBeEmpty();
});

it('cannot cancel a past booking', function (): void {
    $booking = ClassBooking::factory()->create([
        'booked_date' => now()->subDay()->toDateString(),
        'status' => BookingStatus::Booked,
    ]);

    expect(fn () => $this->service->cancel($booking))
        ->toThrow(ValidationException::class);
});

it('cannot cancel an already cancelled booking', function (): void {
    $booking = ClassBooking::factory()->create([
        'booked_date' => now()->addDay()->toDateString(),
        'status' => BookingStatus::Cancelled,
    ]);

    expect(fn () => $this->service->cancel($booking))
        ->toThrow(ValidationException::class);
});

it('cannot book if class is inactive', function (): void {
    $member = Member::factory()->create();
    $gymClass = GymClass::factory()->inactive()->create();
    $monday = Carbon::now()->next(Carbon::MONDAY);
    $schedule = ClassSchedule::factory()->create([
        'gym_class_id' => $gymClass->id,
        'day_of_week' => $monday->dayOfWeek,
        'is_active' => true,
    ]);

    expect(fn () => $this->service->book($member, $schedule, $monday))
        ->toThrow(ValidationException::class);
});

it('cannot book if date does not match schedule day', function (): void {
    $member = Member::factory()->create();
    $gymClass = GymClass::factory()->create(['is_active' => true]);
    $schedule = ClassSchedule::factory()->create([
        'gym_class_id' => $gymClass->id,
        'day_of_week' => Carbon::MONDAY,
        'is_active' => true,
    ]);

    // Pass a Tuesday — should fail
    $tuesday = Carbon::now()->next(Carbon::TUESDAY);

    expect(fn () => $this->service->book($member, $schedule, $tuesday))
        ->toThrow(ValidationException::class);
});

it('getAvailableSlots counts correctly', function (): void {
    $gymClass = GymClass::factory()->create(['capacity' => 5, 'is_active' => true]);
    $monday = Carbon::now()->next(Carbon::MONDAY);
    $schedule = ClassSchedule::factory()->create([
        'gym_class_id' => $gymClass->id,
        'day_of_week' => $monday->dayOfWeek,
        'is_active' => true,
    ]);

    ClassBooking::factory()->count(3)->create([
        'class_schedule_id' => $schedule->id,
        'booked_date' => $monday->toDateString(),
        'status' => BookingStatus::Booked,
    ]);

    // Cancelled bookings should not count
    ClassBooking::factory()->create([
        'class_schedule_id' => $schedule->id,
        'booked_date' => $monday->toDateString(),
        'status' => BookingStatus::Cancelled,
    ]);

    expect($this->service->getAvailableSlots($schedule->load('gymClass'), $monday))->toBe(2);
});

it('getAvailableSlots returns 0 when gymClass relation is not loaded', function (): void {
    $schedule = ClassSchedule::factory()->create(['is_active' => true]);
    $monday = Carbon::now()->next(Carbon::MONDAY);

    // Schedule without a loaded gymClass returns 0
    $scheduleWithoutClass = ClassSchedule::make(['id' => 9999, 'gym_class_id' => null]);

    expect($this->service->getAvailableSlots($scheduleWithoutClass, $monday))->toBe(0);
});

it('cannot book when schedule is inactive', function (): void {
    $member = Member::factory()->create();
    $gymClass = GymClass::factory()->create(['capacity' => 10, 'is_active' => true]);
    $monday = Carbon::now()->next(Carbon::MONDAY);
    $schedule = ClassSchedule::factory()->create([
        'gym_class_id' => $gymClass->id,
        'day_of_week' => $monday->dayOfWeek,
        'is_active' => false,
    ]);

    expect(fn () => $this->service->book($member, $schedule, $monday))
        ->toThrow(ValidationException::class);
});

it('cancel with allowPast=true allows cancelling a past booking', function (): void {
    $booking = ClassBooking::factory()->create([
        'booked_date' => now()->subDays(3)->toDateString(),
        'status' => BookingStatus::Booked,
    ]);

    $this->service->cancel($booking, allowPast: true);

    expect($booking->fresh()->status)->toBe(BookingStatus::Cancelled);
});
