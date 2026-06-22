<?php

namespace App\Models;

use App\Models\Concerns\CascadesSoftDeletes;
use Database\Factories\GymClassFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property int|null $instructor_id
 * @property int $capacity
 * @property int $duration_minutes
 * @property string $color
 * @property bool $is_active
 * @property Carbon|null $deleted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'name',
    'description',
    'instructor_id',
    'capacity',
    'duration_minutes',
    'color',
    'is_active',
])]
class GymClass extends Model
{
    /** @use HasFactory<GymClassFactory> */
    use CascadesSoftDeletes, HasFactory, SoftDeletes;

    /** @return BelongsTo<User, $this> */
    public function instructor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    /** @return HasMany<ClassSchedule, $this> */
    public function schedules(): HasMany
    {
        return $this->hasMany(ClassSchedule::class);
    }

    /** @return HasManyThrough<ClassBooking, ClassSchedule, $this> */
    public function bookings(): HasManyThrough
    {
        return $this->hasManyThrough(ClassBooking::class, ClassSchedule::class);
    }

    /**
     * @return list<string>
     */
    protected static function relationsToCascade(): array
    {
        return ['schedules'];
    }
}
