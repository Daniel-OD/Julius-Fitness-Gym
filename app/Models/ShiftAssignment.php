<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

#[Fillable([
    'user_id',
    'shift_id',
    'valid_from',
    'valid_until',
    'notes',
])]
class ShiftAssignment extends Model
{
    protected $casts = [
        'valid_from' => 'date',
        'valid_until' => 'date',
    ];

    /**
     * @return BelongsTo<User, $this>
     */
    use SoftDeletes;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<Shift, $this>
     */
    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    public function isValidOn(Carbon $date): bool
    {
        if ($date->lt($this->valid_from)) {
            return false;
        }

        if ($this->valid_until !== null && $date->gt($this->valid_until)) {
            return false;
        }

        return true;
    }
}
