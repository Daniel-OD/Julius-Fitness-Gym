<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'category_id' => ProductCategory::factory(),
            'name' => fake()->words(3, true),
            'code' => 'PRD-'.fake()->unique()->numerify('#####'),
            'description' => fake()->optional()->sentence(),
            'price' => fake()->randomFloat(2, 5, 200),
            'cost_price' => fake()->randomFloat(2, 2, 100),
            'images' => [],
            'unit' => 'pcs',
            'is_active' => true,
            'track_stock' => true,
        ];
    }

    public function withoutStockTracking(): static
    {
        return $this->state(['track_stock' => false]);
    }

    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }
}
