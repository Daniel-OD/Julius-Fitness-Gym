<?php

namespace Database\Factories;

use App\Models\FoodItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<FoodItem> */
class FoodItemFactory extends Factory
{
    protected $model = FoodItem::class;

    public function definition(): array
    {
        return [
            'name' => fake()->words(2, true),
            'brand' => fake()->optional()->company(),
            'calories_per_100g' => fake()->randomFloat(2, 50, 500),
            'protein' => fake()->randomFloat(2, 0, 30),
            'carbs' => fake()->randomFloat(2, 0, 80),
            'fat' => fake()->randomFloat(2, 0, 40),
            'fiber' => fake()->randomFloat(2, 0, 15),
            'serving_size' => 100,
            'serving_unit' => 'g',
            'is_verified' => true,
        ];
    }
}
