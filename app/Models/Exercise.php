<?php

namespace App\Models;

use App\Enums\ExerciseCategory;
use Database\Factories\ExerciseFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'name',
    'category',
    'muscle_groups',
    'equipment',
    'instructions',
    'video_url',
    'image',
    'is_active',
])]
class Exercise extends Model
{
    /** @use HasFactory<ExerciseFactory> */
    use HasFactory;

    protected $table = 'exercise_library';

    protected $casts = [
        'category' => ExerciseCategory::class,
        'muscle_groups' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * @return HasMany<WorkoutTemplateExercise, $this>
     */
    public function templateExercises(): HasMany
    {
        return $this->hasMany(WorkoutTemplateExercise::class, 'exercise_id');
    }

    /**
     * @return HasMany<WorkoutPlanExercise, $this>
     */
    public function planExercises(): HasMany
    {
        return $this->hasMany(WorkoutPlanExercise::class, 'exercise_id');
    }
}
