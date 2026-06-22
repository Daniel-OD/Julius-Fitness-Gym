<?php

namespace Database\Factories;

use App\Models\Member;
use App\Models\NutritionPlan;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<NutritionPlan> */
class NutritionPlanFactory extends Factory
{
    protected $model = NutritionPlan::class;

    public function definition(): array
    {
        return [
            'member_id' => Member::factory(),
            'assigned_by' => User::factory(),
            'name' => fake()->words(3, true),
            'daily_calories' => 2200,
            'protein_g' => 165,
            'carbs_g' => 220,
            'fat_g' => 73,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addWeeks(4)->toDateString(),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
