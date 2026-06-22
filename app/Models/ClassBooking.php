<?php

namespace App\Models;

use App\Enums\BookingStatus;
use App\Models\Concerns\CascadesSoftDeletes;
use Database\Factories\ClassBookingFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $member_id
 * @property int $class_schedule_id
 * @property Carbon $booked_date
 * @property BookingStatus $status
 * @property Carbon|null $deleted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'member_id',
    'class_schedule_id',
    'booked_date',
    'status',
])]
class ClassBooking extends Model
{
    /** @use HasFactory<ClassBookingFactory> */
    use CascadesSoftDeletes, HasFactory, SoftDeletes;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'booked_date' => 'date',
            'status' => BookingStatus::class,
        ];
    }

    /** @return BelongsTo<Member, $this> */
    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    /** @return BelongsTo<ClassSchedule, $this> */
    public function schedule(): BelongsTo
    {
        return $this->belongsTo(ClassSchedule::class, 'class_schedule_id');
    }

    /** @param Builder<ClassBooking> $query */
    public function scopeForDate(Builder $query, Carbon $date): void
    {
        $query->whereDate('booked_date', $date);
    }
}
