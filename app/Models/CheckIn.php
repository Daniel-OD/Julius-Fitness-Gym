<?php

namespace App\Models;

use App\Enums\CheckInStatus;
use Database\Factories\CheckInFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $member_id
 * @property int|null $subscription_id
 * @property Carbon $checked_in_at
 * @property Carbon|null $checked_out_at
 * @property CheckInStatus $status
 * @property string|null $denied_reason
 * @property string $method
 * @property string|null $note
 */
class CheckIn extends Model
{
    /** @use HasFactory<CheckInFactory> */
    use HasFactory, SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'member_id',
        'subscription_id',
        'checked_in_at',
        'checked_out_at',
        'status',
        'denied_reason',
        'method',
        'note',
    ];

    protected $casts = [
        'checked_in_at' => 'datetime',
        'checked_out_at' => 'datetime',
        'status' => CheckInStatus::class,
    ];

    /**
     * @return BelongsTo<Member, $this>
     */
    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    /**
     * @return BelongsTo<Subscription, $this>
     */
    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    /**
     * Duration of the visit in minutes, or null if not checked out.
     */
    public function durationMinutes(): ?int
    {
        if (! $this->checked_out_at) {
            return null;
        }

        return (int) $this->checked_in_at->diffInMinutes($this->checked_out_at);
    }
}
