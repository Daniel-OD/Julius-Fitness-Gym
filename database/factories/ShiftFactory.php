<?php

namespace Database\Factories;

use App\Models\Shift;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Shift>
 */
class ShiftFactory extends Factory
{
    protected $model = Shift::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->randomElement(['Morning', 'Afternoon', 'Evening']).' shift',
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
            'days_of_week' => [1, 2, 3, 4, 5],
            'color' => '#6366f1',
            'is_active' => true,
        ];
    }
}
