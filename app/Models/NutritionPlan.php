<?php

namespace App\Models;

use Database\Factories\NutritionPlanFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

#[Fillable([
    'member_id',
    'assigned_by',
    'name',
    'daily_calories',
    'protein_g',
    'carbs_g',
    'fat_g',
    'start_date',
    'end_date',
    'notes',
])]
class NutritionPlan extends Model
{
    /** @use HasFactory<NutritionPlanFactory> */
    use HasFactory;

    protected $casts = [
        'daily_calories' => 'integer',
        'protein_g' => 'decimal:1',
        'carbs_g' => 'decimal:1',
        'fat_g' => 'decimal:1',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    /**
     * @return BelongsTo<Member, $this>
     */
    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function assigner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    /**
     * @return HasMany<NutritionPlanMeal, $this>
     */
    public function meals(): HasMany
    {
        return $this->hasMany(NutritionPlanMeal::class, 'plan_id')->orderBy('order');
    }

    public function isActiveOn(Carbon $date): bool
    {
        if ($this->start_date->gt($date)) {
            return false;
        }

        return $this->end_date === null || $this->end_date->gte($date);
    }
}
