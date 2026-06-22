<?php

namespace App\Models;

use App\Enums\WorkoutDifficulty;
use Database\Factories\WorkoutTemplateFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'name',
    'description',
    'created_by',
    'difficulty',
    'duration_minutes',
    'is_public',
])]
class WorkoutTemplate extends Model
{
    /** @use HasFactory<WorkoutTemplateFactory> */
    use HasFactory;

    protected $casts = [
        'difficulty' => WorkoutDifficulty::class,
        'is_public' => 'boolean',
    ];

    /**
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * @return HasMany<WorkoutTemplateExercise, $this>
     */
    public function exercises(): HasMany
    {
        return $this->hasMany(WorkoutTemplateExercise::class, 'template_id')->orderBy('order');
    }
}
