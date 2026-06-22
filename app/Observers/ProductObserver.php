<?php

namespace App\Observers;

use App\Models\Product;
use App\Models\StockLevel;

class ProductObserver
{
    public function creating(Product $product): void
    {
        if (blank($product->code)) {
            $product->code = $this->generateCode();
        }
    }

    public function created(Product $product): void
    {
        if ($product->track_stock) {
            StockLevel::query()->firstOrCreate(
                ['product_id' => $product->id],
                ['quantity' => 0],
            );
        }
    }

    private function generateCode(): string
    {
        $lastId = (int) Product::withTrashed()->max('id');

        return 'PRD-'.str_pad((string) ($lastId + 1), 5, '0', STR_PAD_LEFT);
    }
}
