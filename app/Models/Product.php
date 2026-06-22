<?php

namespace App\Models;

use App\Models\Concerns\CascadesSoftDeletes;
use App\Observers\ProductObserver;
use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

#[ObservedBy(ProductObserver::class)]
#[Fillable([
    'category_id',
    'name',
    'code',
    'description',
    'price',
    'cost_price',
    'images',
    'unit',
    'is_active',
    'track_stock',
])]
class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use CascadesSoftDeletes, HasFactory, SoftDeletes;

    protected $casts = [
        'price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'images' => 'array',
        'is_active' => 'boolean',
        'track_stock' => 'boolean',
    ];

    /**
     * @return BelongsTo<ProductCategory, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }

    /**
     * @return HasOne<StockLevel, $this>
     */
    public function stockLevel(): HasOne
    {
        return $this->hasOne(StockLevel::class);
    }

    /**
     * @return HasMany<StockMovement, $this>
     */
    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    /**
     * @return HasMany<SaleItem, $this>
     */
    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function currentStock(): int
    {
        if (! $this->track_stock) {
            return PHP_INT_MAX;
        }

        return (int) ($this->stockLevel?->quantity ?? 0);
    }

    public function isLowStock(?int $threshold = null): bool
    {
        if (! $this->track_stock) {
            return false;
        }

        $threshold ??= (int) config('shop.low_stock_threshold', 5);

        return $this->currentStock() < $threshold;
    }

    /**
     * @return list<string>
     */
    protected static function relationsToCascade(): array
    {
        return ['stockLevel', 'stockMovements', 'saleItems'];
    }
}
