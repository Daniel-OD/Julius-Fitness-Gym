<?php

namespace Database\Factories;

use App\Enums\ExerciseCategory;
use App\Models\Exercise;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Exercise> */
class ExerciseFactory extends Factory
{
    protected $model = Exercise::class;

    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true),
            'category' => fake()->randomElement(ExerciseCategory::cases()),
            'muscle_groups' => ['chest', 'triceps'],
            'equipment' => fake()->randomElement(['barbell', 'dumbbell', 'bodyweight', 'machine']),
            'instructions' => fake()->sentence(),
            'is_active' => true,
        ];
    }
}
