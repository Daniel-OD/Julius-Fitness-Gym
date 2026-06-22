<?php

namespace App\Models;

use App\Enums\MealType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NutritionPlanMeal extends Model
{
    protected $fillable = [
        'plan_id',
        'meal_type',
        'name',
        'order',
    ];

    protected $casts = [
        'meal_type' => MealType::class,
        'order' => 'integer',
    ];

    /**
     * @return BelongsTo<NutritionPlan, $this>
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(NutritionPlan::class, 'plan_id');
    }

    /**
     * @return HasMany<NutritionPlanMealItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(NutritionPlanMealItem::class, 'meal_id');
    }
}
