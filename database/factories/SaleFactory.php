<?php

namespace Database\Factories;

use App\Enums\SaleStatus;
use App\Models\Member;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Sale>
 */
class SaleFactory extends Factory
{
    protected $model = Sale::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'member_id' => Member::factory(),
            'user_id' => User::factory(),
            'total' => fake()->randomFloat(2, 10, 500),
            'payment_method' => fake()->randomElement(['cash', 'card', 'online']),
            'status' => SaleStatus::Completed,
            'note' => fake()->optional()->sentence(),
        ];
    }

    public function pending(): static
    {
        return $this->state(['status' => SaleStatus::Pending]);
    }

    public function cancelled(): static
    {
        return $this->state(['status' => SaleStatus::Cancelled]);
    }
}
