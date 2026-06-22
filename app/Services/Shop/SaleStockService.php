<?php

namespace App\Services\Shop;

use App\Enums\StockMovementType;
use App\Models\Sale;
use App\Models\StockMovement;

final class SaleStockService
{
    public function deductStockForSale(Sale $sale): void
    {
        if ($sale->stockMovements()->exists()) {
            return;
        }

        $sale->loadMissing('items.product');

        foreach ($sale->items as $item) {
            if (! $item->product?->track_stock) {
                continue;
            }

            StockMovement::query()->create([
                'product_id' => $item->product_id,
                'type' => StockMovementType::Out,
                'quantity' => $item->quantity,
                'reference_type' => Sale::class,
                'reference_id' => $sale->id,
                'note' => __('app.shop.notes.sale_deduction'),
                'created_by' => $sale->user_id,
            ]);
        }
    }
}
