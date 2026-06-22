<?php

namespace Database\Factories;

use App\Enums\PayrollItemStatus;
use App\Models\PayrollItem;
use App\Models\PayrollPeriod;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PayrollItem>
 */
class PayrollItemFactory extends Factory
{
    protected $model = PayrollItem::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $base = fake()->randomFloat(2, 2500, 8000);

        return [
            'period_id' => PayrollPeriod::factory(),
            'user_id' => User::factory(),
            'base_salary' => $base,
            'working_days' => 22,
            'present_days' => 20,
            'overtime_hours' => 0,
            'deductions' => [],
            'bonuses' => [],
            'gross' => $base,
            'net' => $base,
            'status' => PayrollItemStatus::Draft,
        ];
    }
}
