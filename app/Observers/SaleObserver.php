<?php

namespace App\Observers;

use App\Enums\SaleStatus;
use App\Models\Sale;
use App\Services\Shop\SaleStockService;

class SaleObserver
{
    public function __construct(private readonly SaleStockService $saleStockService) {}

    public function updated(Sale $sale): void
    {
        if (! $sale->wasChanged('status')) {
            return;
        }

        if ($sale->status !== SaleStatus::Completed) {
            return;
        }

        if ($sale->stockMovements()->exists()) {
            return;
        }

        $this->saleStockService->deductStockForSale($sale);
    }
}
