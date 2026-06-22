<?php

namespace App\Models;

use App\Enums\PayrollItemStatus;
use Database\Factories\PayrollItemFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'period_id',
    'user_id',
    'base_salary',
    'working_days',
    'present_days',
    'overtime_hours',
    'deductions',
    'bonuses',
    'gross',
    'net',
    'status',
])]
class PayrollItem extends Model
{
    /** @use HasFactory<PayrollItemFactory> */
    use HasFactory, SoftDeletes;

    protected $casts = [
        'base_salary' => 'decimal:2',
        'present_days' => 'decimal:2',
        'overtime_hours' => 'decimal:2',
        'deductions' => 'array',
        'bonuses' => 'array',
        'gross' => 'decimal:2',
        'net' => 'decimal:2',
        'status' => PayrollItemStatus::class,
    ];

    /**
     * @return BelongsTo<PayrollPeriod, $this>
     */
    public function period(): BelongsTo
    {
        return $this->belongsTo(PayrollPeriod::class, 'period_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @param  array<int, array{label: string, amount: float|int|string}>|null  $items
     */
    public static function sumAdjustments(?array $items): float
    {
        if (! is_array($items)) {
            return 0.0;
        }

        return round(collect($items)->sum(fn (array $item): float => (float) ($item['amount'] ?? 0)), 2);
    }
}
