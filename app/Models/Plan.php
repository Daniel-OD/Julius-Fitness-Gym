<?php

namespace App\Models;

use App\Enums\Status;
use App\Models\Concerns\CascadesSoftDeletes;
use Database\Factories\PlanFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int|null $service_id
 * @property string|null $name
 * @property string|null $code
 * @property string|null $description
 * @property int|null $days
 * @property float|null $amount
 * @property Status|null $status
 */
class Plan extends Model
{
    /** @use HasFactory<PlanFactory> */
    use CascadesSoftDeletes, HasFactory, SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'code',
        'description',
        'service_id',
        'amount',
        'days',
        'status',
    ];

    protected $casts = [
        'status' => Status::class,
    ];

    /**
     * @return BelongsTo<Service, $this>
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    /**
     * @return HasMany<Subscription, $this>
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    /**
     * @return list<string>
     */
    protected static function relationsToCascade(): array
    {
        return ['subscriptions'];
    }
}
