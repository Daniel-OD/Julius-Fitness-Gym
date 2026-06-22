<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkoutPlanDay extends Model
{
    protected $fillable = [
        'plan_id',
        'day_number',
        'name',
        'template_id',
        'notes',
    ];

    protected $casts = [
        'day_number' => 'integer',
    ];

    /**
     * @return BelongsTo<MemberWorkoutPlan, $this>
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(MemberWorkoutPlan::class, 'plan_id');
    }

    /**
     * @return BelongsTo<WorkoutTemplate, $this>
     */
    public function template(): BelongsTo
    {
        return $this->belongsTo(WorkoutTemplate::class, 'template_id');
    }

    /**
     * @return HasMany<WorkoutPlanExercise, $this>
     */
    public function exercises(): HasMany
    {
        return $this->hasMany(WorkoutPlanExercise::class, 'plan_day_id')->orderBy('order');
    }

    /**
     * @return HasMany<WorkoutLog, $this>
     */
    public function logs(): HasMany
    {
        return $this->hasMany(WorkoutLog::class, 'plan_day_id');
    }
}
