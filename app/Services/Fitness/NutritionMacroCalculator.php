<?php

namespace App\Services\Fitness;

use App\Models\FoodItem;

/**
 * Calculates nutrition macros from food items and quantities.
 */
final class NutritionMacroCalculator
{
    /**
     * @return array{calories: float, protein: float, carbs: float, fat: float, fiber: float}
     */
    public function forFoodItem(FoodItem $foodItem, float $quantity, string $unit = 'g'): array
    {
        $grams = $this->resolveGrams($foodItem, $quantity, $unit);
        $factor = $grams / 100;

        return [
            'calories' => $this->round($foodItem->calories_per_100g * $factor),
            'protein' => $this->round($foodItem->protein * $factor),
            'carbs' => $this->round($foodItem->carbs * $factor),
            'fat' => $this->round($foodItem->fat * $factor),
            'fiber' => $this->round($foodItem->fiber * $factor),
        ];
    }

    /**
     * @param  iterable<int, array{food_item: FoodItem, quantity: float, unit: string}>  $entries
     * @return array{calories: float, protein: float, carbs: float, fat: float, fiber: float}
     */
    public function sum(iterable $entries): array
    {
        $totals = [
            'calories' => 0.0,
            'protein' => 0.0,
            'carbs' => 0.0,
            'fat' => 0.0,
            'fiber' => 0.0,
        ];

        foreach ($entries as $entry) {
            $macros = $this->forFoodItem(
                $entry['food_item'],
                (float) $entry['quantity'],
                (string) $entry['unit'],
            );

            foreach ($totals as $key => $value) {
                $totals[$key] += $macros[$key];
            }
        }

        return array_map(fn (float $value): float => $this->round($value), $totals);
    }

    /**
     * Estimate daily macro targets from calories using a 30/40/30 split.
     *
     * @return array{protein_g: float, carbs_g: float, fat_g: float}
     */
    public function targetsFromCalories(int $calories): array
    {
        return [
            'protein_g' => $this->round(($calories * 0.30) / 4),
            'carbs_g' => $this->round(($calories * 0.40) / 4),
            'fat_g' => $this->round(($calories * 0.30) / 9),
        ];
    }

    private function resolveGrams(FoodItem $foodItem, float $quantity, string $unit): float
    {
        $unit = strtolower(trim($unit));

        if (in_array($unit, ['g', 'gram', 'grams'], true)) {
            return max(0, $quantity);
        }

        if (in_array($unit, ['serving', 'servings', 'portion', 'portions'], true)) {
            return max(0, $quantity * (float) $foodItem->serving_size);
        }

        return max(0, $quantity);
    }

    private function round(float $value): float
    {
        return round($value, 1);
    }
}
