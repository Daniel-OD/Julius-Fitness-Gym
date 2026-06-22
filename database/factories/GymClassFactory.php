<?php

namespace Database\Factories;

use App\Models\GymClass;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GymClass>
 */
class GymClassFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->randomElement(['HIIT', 'Yoga', 'Spinning', 'Pilates', 'Zumba', 'Boxing', 'Strength']),
            'description' => $this->faker->sentence(),
            'instructor_id' => null,
            'capacity' => $this->faker->numberBetween(5, 30),
            'duration_minutes' => $this->faker->randomElement([30, 45, 60, 90]),
            'color' => $this->faker->hexColor(),
            'is_active' => true,
        ];
    }

    public function withInstructor(): static
    {
        return $this->state(fn (): array => ['instructor_id' => User::factory()]);
    }

    public function inactive(): static
    {
        return $this->state(fn (): array => ['is_active' => false]);
    }
}
