<?php

namespace Database\Factories;

use App\Models\ClassSchedule;
use App\Models\GymClass;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ClassSchedule>
 */
class ClassScheduleFactory extends Factory
{
    public function definition(): array
    {
        return [
            'gym_class_id' => GymClass::factory(),
            'day_of_week' => $this->faker->numberBetween(0, 6),
            'start_time' => $this->faker->randomElement(['07:00', '09:00', '11:00', '14:00', '17:00', '19:00']),
            'location' => $this->faker->randomElement(['Studio A', 'Studio B', 'Main Hall', null]),
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (): array => ['is_active' => false]);
    }

    public function forDay(int $dayOfWeek): static
    {
        return $this->state(fn (): array => ['day_of_week' => $dayOfWeek]);
    }
}
