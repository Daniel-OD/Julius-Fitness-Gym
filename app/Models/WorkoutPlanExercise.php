<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkoutPlanExercise extends Model
{
    protected $fillable = [
        'plan_day_id',
        'exercise_id',
        'sets',
        'reps',
        'duration_seconds',
        'rest_seconds',
        'order',
        'notes',
    ];

    protected $casts = [
        'sets' => 'integer',
        'reps' => 'integer',
        'duration_seconds' => 'integer',
        'rest_seconds' => 'integer',
        'order' => 'integer',
    ];

    /**
     * @return BelongsTo<WorkoutPlanDay, $this>
     */
    public function planDay(): BelongsTo
    {
        return $this->belongsTo(WorkoutPlanDay::class, 'plan_day_id');
    }

    /**
     * @return BelongsTo<Exercise, $this>
     */
    public function exercise(): BelongsTo
    {
        return $this->belongsTo(Exercise::class, 'exercise_id');
    }
}
