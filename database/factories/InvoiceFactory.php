<?php

namespace Database\Factories;

use App\Helpers\Helpers;
use App\Models\Invoice;
use App\Models\Subscription;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Invoice>
 */
class InvoiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $date = $this->faker->dateTimeBetween('-1 year', 'now');
        $dueDate = (clone $date)->modify('+30 days');
        $fee = $this->faker->randomFloat(2, 50, 1000);
        $discountOptions = array_keys(Helpers::getDiscounts());
        $discountPct = $discountOptions ? $this->faker->randomElement($discountOptions) : 0;
        $discountAmount = round(Helpers::getDiscountAmount((float) $discountPct, $fee), 2);
        $paidAmount = $this->faker->randomFloat(2, 0, $fee);

        return [
            'number' => $this->faker->unique()->numerify('INV-#####'),
            'subscription_id' => Subscription::factory(),
            'date' => $date,
            'due_date' => $dueDate,
            'payment_method' => $this->faker->randomElement(['cash', 'online', 'bank_transfer']),
            'status' => 'issued',
            'subscription_fee' => $fee,
            'discount' => $discountPct,
            'discount_amount' => $discountAmount,
            'discount_note' => $this->faker->optional()->sentence(3),
            'paid_amount' => $paidAmount,
        ];
    }
}
