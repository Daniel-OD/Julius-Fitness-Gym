<?php

namespace App\Models;

use App\Enums\MealType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FoodLog extends Model
{
    protected $fillable = [
        'member_id',
        'logged_at',
        'meal_type',
        'food_item_id',
        'quantity',
        'unit',
    ];

    protected $casts = [
        'logged_at' => 'date',
        'meal_type' => MealType::class,
        'quantity' => 'decimal:2',
    ];

    /**
     * @return BelongsTo<Member, $this>
     */
    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    /**
     * @return BelongsTo<FoodItem, $this>
     */
    public function foodItem(): BelongsTo
    {
        return $this->belongsTo(FoodItem::class);
    }
}
