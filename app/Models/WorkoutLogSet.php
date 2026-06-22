<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkoutLogSet extends Model
{
    protected $fillable = [
        'log_id',
        'exercise_id',
        'set_number',
        'reps',
        'weight',
        'duration_seconds',
        'rest_seconds',
    ];

    protected $casts = [
        'set_number' => 'integer',
        'reps' => 'integer',
        'weight' => 'decimal:2',
        'duration_seconds' => 'integer',
        'rest_seconds' => 'integer',
    ];

    /**
     * @return BelongsTo<WorkoutLog, $this>
     */
    public function log(): BelongsTo
    {
        return $this->belongsTo(WorkoutLog::class, 'log_id');
    }

    /**
     * @return BelongsTo<Exercise, $this>
     */
    public function exercise(): BelongsTo
    {
        return $this->belongsTo(Exercise::class, 'exercise_id');
    }
}
