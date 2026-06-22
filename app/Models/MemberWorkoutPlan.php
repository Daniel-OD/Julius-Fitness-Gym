<?php

namespace App\Models;

use App\Enums\WorkoutPlanStatus;
use Database\Factories\MemberWorkoutPlanFactory;
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
    'start_date',
    'end_date',
    'notes',
    'status',
])]
class MemberWorkoutPlan extends Model
{
    /** @use HasFactory<MemberWorkoutPlanFactory> */
    use HasFactory;

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'status' => WorkoutPlanStatus::class,
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
     * @return HasMany<WorkoutPlanDay, $this>
     */
    public function days(): HasMany
    {
        return $this->hasMany(WorkoutPlanDay::class, 'plan_id')->orderBy('day_number');
    }

    public function isActiveOn(Carbon $date): bool
    {
        if ($this->status !== WorkoutPlanStatus::Active) {
            return false;
        }

        if ($this->start_date->gt($date)) {
            return false;
        }

        return $this->end_date === null || $this->end_date->gte($date);
    }
}
