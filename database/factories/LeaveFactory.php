<?php

namespace Database\Factories;

use App\Enums\LeaveStatus;
use App\Enums\LeaveType;
use App\Models\Leave;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Leave>
 */
class LeaveFactory extends Factory
{
    protected $model = Leave::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $start = fake()->dateTimeBetween('-1 month', '+1 month');
        $end = (clone $start)->modify('+'.fake()->numberBetween(1, 5).' days');

        return [
            'user_id' => User::factory(),
            'type' => LeaveType::Annual,
            'start_date' => $start->format('Y-m-d'),
            'end_date' => $end->format('Y-m-d'),
            'days' => $start->diff($end)->days + 1,
            'status' => LeaveStatus::Pending,
            'reason' => fake()->sentence(),
            'approved_by' => null,
            'approved_at' => null,
        ];
    }
}
