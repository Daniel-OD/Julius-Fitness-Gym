<?php

namespace App\Observers;

use App\Enums\StockMovementType;
use App\Models\StockLevel;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;

class StockMovementObserver
{
    public function creating(StockMovement $movement): void
    {
        $level = StockLevel::query()->firstOrCreate(
            ['product_id' => $movement->product_id],
            ['quantity' => 0],
        );

        $before = (int) $level->quantity;
        $movement->quantity_before = $before;

        $after = match ($movement->type) {
            StockMovementType::In => $before + (int) $movement->quantity,
            StockMovementType::Out => $before - (int) $movement->quantity,
            StockMovementType::Adjustment => (int) $movement->quantity,
        };

        if ($after < 0) {
            throw new \InvalidArgumentException(__('app.shop.errors.negative_stock'));
        }

        $movement->quantity_after = $after;
    }

    public function created(StockMovement $movement): void
    {
        DB::transaction(function () use ($movement): void {
            StockLevel::query()->updateOrCreate(
                ['product_id' => $movement->product_id],
                ['quantity' => $movement->quantity_after],
            );
        });
    }
}
