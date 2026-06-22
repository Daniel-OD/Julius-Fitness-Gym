<?php

namespace App\Models;

use App\Models\Concerns\CascadesSoftDeletes;
use Database\Factories\ClassScheduleFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * @property int $id
 * @property int $gym_class_id
 * @property int $day_of_week 0=Sunday … 6=Saturday
 * @property string $start_time HH:MM:SS
 * @property string|null $location
 * @property bool $is_active
 * @property Carbon|null $deleted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'gym_class_id',
    'day_of_week',
    'start_time',
    'location',
    'is_active',
])]
class ClassSchedule extends Model
{
    /** @use HasFactory<ClassScheduleFactory> */
    use CascadesSoftDeletes, HasFactory, SoftDeletes;

    /** @return BelongsTo<GymClass, $this> */
    public function gymClass(): BelongsTo
    {
        return $this->belongsTo(GymClass::class);
    }

    /** @return HasMany<ClassBooking, $this> */
    public function bookings(): HasMany
    {
        return $this->hasMany(ClassBooking::class);
    }

    /**
     * Returns Carbon dates in [$from, $to] range that match this schedule's day_of_week.
     *
     * @return Collection<int, Carbon>
     */
    public function occurrencesInRange(Carbon $from, Carbon $to): Collection
    {
        $dates = collect();
        $current = $from->copy()->startOfDay();

        while ($current->lte($to)) {
            if ($current->dayOfWeek === $this->day_of_week) {
                $dates->push($current->copy());
            }
            $current->addDay();
        }

        return $dates;
    }

    /**
     * @return list<string>
     */
    protected static function relationsToCascade(): array
    {
        return ['bookings'];
    }
}
