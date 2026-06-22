<?php

namespace Database\Factories;

use App\Enums\SalaryType;
use App\Models\StaffProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StaffProfile>
 */
class StaffProfileFactory extends Factory
{
    protected $model = StaffProfile::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'department' => fake()->randomElement(['Front desk', 'Coaching', 'Administration']),
            'position' => fake()->jobTitle(),
            'hire_date' => fake()->dateTimeBetween('-3 years', '-1 month'),
            'base_salary' => fake()->randomFloat(2, 2500, 8000),
            'salary_type' => SalaryType::Monthly,
            'bank_details' => [
                'iban' => fake()->iban('RO'),
                'bank_name' => fake()->company(),
            ],
            'emergency_contact' => fake()->phoneNumber(),
            'notes' => null,
        ];
    }
}
