<?php

namespace App\Models;

use App\Enums\Status;
use App\Observers\SubscriptionObserver;
use Database\Factories\SubscriptionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int|null $renewed_from_subscription_id
 * @property int $member_id
 * @property int $plan_id
 * @property Carbon $start_date
 * @property Carbon $end_date
 * @property Status|null $status
 * @property string $type official|internal
 * @property string|null $internal_note
 */
#[ObservedBy(SubscriptionObserver::class)]
#[Fillable([
    'renewed_from_subscription_id',
    'member_id',
    'plan_id',
    'start_date',
    'end_date',
    'status',
    'type',
    'internal_note',
])]
class Subscription extends Model
{
    /** @use HasFactory<SubscriptionFactory> */
    use HasFactory, SoftDeletes;

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'status' => Status::class,
    ];

    /**
     * @return HasMany<Invoice, $this>
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * @return BelongsTo<Subscription, $this>
     */
    public function renewedFrom(): BelongsTo
    {
        return $this->belongsTo(self::class, 'renewed_from_subscription_id');
    }

    /**
     * @return HasMany<Subscription, $this>
     */
    public function renewals(): HasMany
    {
        return $this->hasMany(self::class, 'renewed_from_subscription_id');
    }

    /**
     * @return BelongsTo<Member, $this>
     */
    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    /**
     * @return BelongsTo<Plan, $this>
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    /**
     * @return HasMany<CheckIn, $this>
     */
    public function checkIns(): HasMany
    {
        return $this->hasMany(CheckIn::class);
    }

    public function isOfficial(): bool
    {
        return $this->type === 'official';
    }

    /**
     * @param  Builder<Subscription>  $query
     * @return Builder<Subscription>
     */
    public function scopeWithoutInvoices(Builder $query): Builder
    {
        return $query->whereDoesntHave('invoices');
    }
}
