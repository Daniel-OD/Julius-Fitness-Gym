<?php

namespace App\Models;

use App\Enums\Status;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int|null $enquiry_id
 * @property int|null $user_id
 * @property Carbon|null $schedule_date
 * @property string|null $method
 * @property string|null $outcome
 * @property Status|null $status
 */
class FollowUp extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'enquiry_id',
        'user_id',
        'schedule_date',
        'method',
        'outcome',
        'status',
    ];

    protected $casts = [
        'schedule_date' => 'date',
        'status' => Status::class,
    ];

    /**
     * @return BelongsTo<Enquiry, $this>
     */
    public function enquiry(): BelongsTo
    {
        return $this->belongsTo(Enquiry::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
