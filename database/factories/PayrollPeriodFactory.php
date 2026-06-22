<?php

namespace Database\Factories;

use App\Enums\PayrollPeriodStatus;
use App\Models\PayrollPeriod;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PayrollPeriod>
 */
class PayrollPeriodFactory extends Factory
{
    protected $model = PayrollPeriod::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'month' => fake()->numberBetween(1, 12),
            'year' => (int) fake()->year(),
            'status' => PayrollPeriodStatus::Draft,
            'generated_at' => now(),
            'approved_by' => null,
        ];
    }
}
