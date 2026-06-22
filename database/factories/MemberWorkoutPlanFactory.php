<?php

namespace Database\Factories;

use App\Enums\WorkoutPlanStatus;
use App\Models\Member;
use App\Models\MemberWorkoutPlan;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<MemberWorkoutPlan> */
class MemberWorkoutPlanFactory extends Factory
{
    protected $model = MemberWorkoutPlan::class;

    public function definition(): array
    {
        return [
            'member_id' => Member::factory(),
            'assigned_by' => User::factory(),
            'name' => fake()->words(3, true),
            'start_date' => now()->toDateString(),
            'end_date' => now()->addWeeks(4)->toDateString(),
            'notes' => fake()->optional()->sentence(),
            'status' => WorkoutPlanStatus::Active,
        ];
    }
}
