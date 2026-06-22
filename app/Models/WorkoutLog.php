<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkoutLog extends Model
{
    protected $fillable = [
        'member_id',
        'plan_day_id',
        'logged_at',
        'duration_minutes',
        'notes',
    ];

    protected $casts = [
        'logged_at' => 'datetime',
        'duration_minutes' => 'integer',
    ];

    /**
     * @return BelongsTo<Member, $this>
     */
    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    /**
     * @return BelongsTo<WorkoutPlanDay, $this>
     */
    public function planDay(): BelongsTo
    {
        return $this->belongsTo(WorkoutPlanDay::class, 'plan_day_id');
    }

    /**
     * @return HasMany<WorkoutLogSet, $this>
     */
    public function sets(): HasMany
    {
        return $this->hasMany(WorkoutLogSet::class, 'log_id')->orderBy('set_number');
    }
}
