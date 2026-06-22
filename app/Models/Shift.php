<?php

namespace App\Models;

use App\Models\Concerns\CascadesSoftDeletes;
use Database\Factories\ShiftFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'name',
    'start_time',
    'end_time',
    'days_of_week',
    'color',
    'is_active',
])]
class Shift extends Model
{
    /** @use HasFactory<ShiftFactory> */
    use CascadesSoftDeletes, HasFactory, SoftDeletes;

    /**
     * @return list<string>
     */
    protected static function relationsToCascade(): array
    {
        return ['assignments'];
    }

    protected $casts = [
        'days_of_week' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * @return HasMany<ShiftAssignment, $this>
     */
    public function assignments(): HasMany
    {
        return $this->hasMany(ShiftAssignment::class);
    }

    public function appliesOnDay(int $dayOfWeek): bool
    {
        $days = $this->days_of_week ?? [];

        return in_array($dayOfWeek, array_map('intval', $days), true);
    }
}
