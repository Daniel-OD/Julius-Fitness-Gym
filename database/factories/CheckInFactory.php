<?php

namespace Database\Factories;

use App\Models\CheckIn;
use App\Models\Member;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CheckIn>
 */
class CheckInFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $checkedInAt = $this->faker->dateTimeBetween('-30 days', 'now');

        return [
            'member_id' => Member::factory(),
            'subscription_id' => null,
            'checked_in_at' => $checkedInAt,
            'checked_out_at' => $this->faker->boolean(70)
                ? $this->faker->dateTimeBetween($checkedInAt, (clone $checkedInAt)->modify('+3 hours'))
                : null,
            'method' => $this->faker->randomElement(['qr', 'manual']),
            'note' => $this->faker->optional(0.2)->sentence(),
        ];
    }
}
