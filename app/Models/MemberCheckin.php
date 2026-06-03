<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Legacy check-in log (superseded by {@see CheckIn}).
 *
 * @deprecated No application code should write to this model. Table may be removed in a future release.
 *
 * @property int $id
 * @property int $member_id
 * @property Carbon $checked_in_at
 */
class MemberCheckin extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'member_id',
        'checked_in_at',
    ];

    protected $casts = [
        'checked_in_at' => 'datetime',
    ];

    /**
     * @return BelongsTo<Member, $this>
     */
    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }
}
