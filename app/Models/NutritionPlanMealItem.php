<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NutritionPlanMealItem extends Model
{
    protected $fillable = [
        'meal_id',
        'food_item_id',
        'quantity',
        'unit',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
    ];

    /**
     * @return BelongsTo<NutritionPlanMeal, $this>
     */
    public function meal(): BelongsTo
    {
        return $this->belongsTo(NutritionPlanMeal::class, 'meal_id');
    }

    /**
     * @return BelongsTo<FoodItem, $this>
     */
    public function foodItem(): BelongsTo
    {
        return $this->belongsTo(FoodItem::class);
    }
}
