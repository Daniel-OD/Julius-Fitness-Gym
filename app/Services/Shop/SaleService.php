<?php

namespace App\Services\Shop;

use App\Enums\SaleStatus;
use App\Enums\StockMovementType;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\StockMovement;
use App\Models\User;
use App\Support\Billing\PaymentMethod;
use App\Support\Data;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * Handles sale creation, stock validation, and stock deductions.
 */
final class SaleService
{
    /**
     * @param  array{
     *   member_id?: int|null,
     *   payment_method?: string|null,
     *   note?: string|null,
     *   status?: string|null,
     *   items: list<array{product_id: int, quantity: int}>
     * }  $data
     */
    public function create(array $data, ?User $cashier = null): Sale
    {
        return DB::transaction(function () use ($data, $cashier): Sale {
            $items = collect($data['items']);

            if ($items->isEmpty()) {
                throw new InvalidArgumentException(__('app.shop.errors.no_items'));
            }

            $products = $this->loadProducts($items);
            $this->validateStock($items, $products);

            $sale = Sale::query()->create([
                'member_id' => $data['member_id'] ?? null,
                'user_id' => $cashier?->id,
                'payment_method' => PaymentMethod::normalize($data['payment_method'] ?? 'cash'),
                'status' => SaleStatus::Pending,
                'note' => Data::nullableString($data['note'] ?? null),
                'total' => 0,
            ]);

            $total = 0.0;

            foreach ($items as $item) {
                $productId = (int) $item['product_id'];
                $quantity = max(1, (int) $item['quantity']);
                /** @var Product $product */
                $product = $products->get($productId);
                $unitPrice = (float) $product->price;
                $subtotal = round($unitPrice * $quantity, 2);

                SaleItem::query()->create([
                    'sale_id' => $sale->id,
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'subtotal' => $subtotal,
                ]);

                $total += $subtotal;
            }

            $sale->update(['total' => round($total, 2)]);

            $targetStatus = SaleStatus::tryFrom(Data::string($data['status'] ?? SaleStatus::Completed->value))
                ?? SaleStatus::Completed;

            if ($targetStatus === SaleStatus::Completed) {
                $sale->update(['status' => SaleStatus::Completed]);
            }

            return $sale->fresh(['items.product', 'member', 'cashier']);
        });
    }

    public function cancel(Sale $sale): Sale
    {
        if ($sale->status === SaleStatus::Cancelled) {
            return $sale;
        }

        if ($sale->status === SaleStatus::Completed) {
            $this->restoreStockForCancelledSale($sale);
        }

        $sale->update(['status' => SaleStatus::Cancelled]);

        return $sale->fresh();
    }

    /**
     * @param  Collection<int, array{product_id: int, quantity: int}>  $items
     * @return Collection<int, Product>
     */
    private function loadProducts(Collection $items): Collection
    {
        $ids = $items->pluck('product_id')->map(fn ($id): int => (int) $id)->unique()->values();

        $products = Product::query()
            ->with('stockLevel')
            ->whereIn('id', $ids)
            ->where('is_active', true)
            ->get()
            ->keyBy('id');

        if ($products->count() !== $ids->count()) {
            throw new InvalidArgumentException(__('app.shop.errors.invalid_products'));
        }

        return $products;
    }

    /**
     * @param  Collection<int, array{product_id: int, quantity: int}>  $items
     * @param  Collection<int, Product>  $products
     */
    public function validateStock(Collection $items, Collection $products): void
    {
        foreach ($items as $item) {
            $productId = (int) $item['product_id'];
            $quantity = max(1, (int) $item['quantity']);
            /** @var Product|null $product */
            $product = $products->get($productId);

            if (! $product instanceof Product) {
                throw new InvalidArgumentException(__('app.shop.errors.invalid_products'));
            }

            if (! $product->track_stock) {
                continue;
            }

            if ($product->currentStock() < $quantity) {
                throw new InvalidArgumentException(__('app.shop.errors.insufficient_stock', [
                    'product' => $product->name,
                    'available' => $product->currentStock(),
                ]));
            }
        }
    }

    private function restoreStockForCancelledSale(Sale $sale): void
    {
        $sale->loadMissing('items.product');

        foreach ($sale->items as $item) {
            if (! $item->product?->track_stock) {
                continue;
            }

            StockMovement::query()->create([
                'product_id' => $item->product_id,
                'type' => StockMovementType::In,
                'quantity' => $item->quantity,
                'reference_type' => Sale::class,
                'reference_id' => $sale->id,
                'note' => __('app.shop.notes.sale_cancelled_restore'),
                'created_by' => auth()->id(),
            ]);
        }
    }
}
