<?php

namespace Database\Factories;

use App\Models\Enquiry;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Enquiry>
 */
class EnquiryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'contact' => $this->faker->phoneNumber(),
            'date' => $this->faker->date(),
            'gender' => $this->faker->randomElement(['male', 'female', 'other']),
            'dob' => $this->faker->date('Y-m-d', '-15 years'),
            'status' => $this->faker->randomElement(['lead', 'member', 'lost']),
            'address' => $this->faker->address(),
            'country' => $this->faker->country(),
            'city' => $this->faker->city(),
            'state' => $this->faker->state(),
            'pincode' => $this->faker->postcode(),
            'interested_in' => $this->faker->randomElements(['Yoga', 'Cardio', 'Strength', 'Swimming', 'Cycling'], rand(1, 3)),
            'source' => $this->faker->randomElement(['promotions', 'word_of_mouth', 'online', 'referral']),
            'goal' => $this->faker->randomElement(['fitness', 'weight loss', 'muscle gain']),
            'start_by' => $this->faker->dateTimeBetween('now', '+1 month')->format('Y-m-d'),
        ];
    }
}
