<?php

namespace Database\Factories;

use App\Enums\CheckInStatus;
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
            'status' => CheckInStatus::Success,
            'method' => $this->faker->randomElement(['qr', 'manual']),
            'note' => $this->faker->optional(0.2)->sentence(),
        ];
    }

    public function graceEntry(): static
    {
        return $this->state(fn (): array => [
            'status' => CheckInStatus::GraceEntry,
            'subscription_id' => null,
        ]);
    }

    public function blocked(string $reason = 'expired_grace_used'): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => CheckInStatus::Blocked,
            'denied_reason' => $reason,
            'subscription_id' => null,
            'checked_out_at' => $attributes['checked_in_at'],
        ]);
    }
}
