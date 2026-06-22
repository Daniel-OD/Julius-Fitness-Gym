<?php

namespace Database\Factories;

use App\Enums\BookingStatus;
use App\Models\ClassBooking;
use App\Models\ClassSchedule;
use App\Models\Member;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ClassBooking>
 */
class ClassBookingFactory extends Factory
{
    public function definition(): array
    {
        return [
            'member_id' => Member::factory(),
            'class_schedule_id' => ClassSchedule::factory(),
            'booked_date' => $this->faker->dateTimeBetween('now', '+1 month')->format('Y-m-d'),
            'status' => BookingStatus::Booked,
        ];
    }

    public function cancelled(): static
    {
        return $this->state(fn (): array => ['status' => BookingStatus::Cancelled]);
    }

    public function attended(): static
    {
        return $this->state(fn (): array => ['status' => BookingStatus::Attended]);
    }
}
