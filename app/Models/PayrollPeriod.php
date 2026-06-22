<?php

namespace App\Models;

use App\Enums\PayrollPeriodStatus;
use App\Models\Concerns\CascadesSoftDeletes;
use Database\Factories\PayrollPeriodFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'month',
    'year',
    'status',
    'generated_at',
    'approved_by',
])]
class PayrollPeriod extends Model
{
    /** @use HasFactory<PayrollPeriodFactory> */
    use CascadesSoftDeletes, HasFactory, SoftDeletes;

    /**
     * @return list<string>
     */
    protected static function relationsToCascade(): array
    {
        return ['items'];
    }

    protected $casts = [
        'status' => PayrollPeriodStatus::class,
        'generated_at' => 'datetime',
    ];

    /**
     * @return HasMany<PayrollItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(PayrollItem::class, 'period_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function label(): string
    {
        return __('app.hr.months.'.$this->month).' '.$this->year;
    }
}
