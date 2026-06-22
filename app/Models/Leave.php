<?php

namespace App\Models;

use App\Enums\LeaveStatus;
use App\Enums\LeaveType;
use Database\Factories\LeaveFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

#[Fillable([
    'user_id',
    'type',
    'start_date',
    'end_date',
    'days',
    'status',
    'reason',
    'approved_by',
    'approved_at',
])]
class Leave extends Model
{
    /** @use HasFactory<LeaveFactory> */
    use HasFactory, SoftDeletes;

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'type' => LeaveType::class,
        'status' => LeaveStatus::class,
        'approved_at' => 'datetime',
    ];

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function coversDate(Carbon $date): bool
    {
        if ($this->status !== LeaveStatus::Approved) {
            return false;
        }

        return $date->betweenIncluded($this->start_date, $this->end_date);
    }
}
