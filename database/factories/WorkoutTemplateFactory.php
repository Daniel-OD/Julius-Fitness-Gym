<?php

namespace Database\Factories;

use App\Enums\WorkoutDifficulty;
use App\Models\User;
use App\Models\WorkoutTemplate;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<WorkoutTemplate> */
class WorkoutTemplateFactory extends Factory
{
    protected $model = WorkoutTemplate::class;

    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true),
            'description' => fake()->sentence(),
            'created_by' => User::factory(),
            'difficulty' => WorkoutDifficulty::Intermediate,
            'duration_minutes' => 45,
            'is_public' => true,
        ];
    }
}
