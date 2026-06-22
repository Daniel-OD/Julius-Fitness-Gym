<?php

namespace App\Services\Api\Schemas;

use App\Models\Sale;
use Illuminate\Contracts\Validation\ValidationRule;

final class SaleSchema
{
    private function __construct() {}

    /**
     * @return array{
     *   searchable: list<string>,
     *   sortable: list<string>,
     *   default_sort: string,
     *   status_column: string|null,
     *   includes: list<string>,
     *   filters: array<string, array{type: string, column: string}>
     * }
     */
    public static function queryRules(): array
    {
        return [
            'searchable' => ['note'],
            'sortable' => ['id', 'created_at', 'total', 'status'],
            'default_sort' => '-id',
            'status_column' => 'status',
            'includes' => ['member', 'cashier', 'items.product'],
            'filters' => [
                'member_id' => ['type' => 'exact', 'column' => 'member_id'],
                'created_at' => ['type' => 'datetime_range', 'column' => 'created_at'],
            ],
        ];
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public static function storeRules(): array
    {
        return [
            'member_id' => ['nullable', 'integer', 'exists:members,id'],
            'payment_method' => ['nullable', 'string', 'max:50'],
            'note' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function resource(Sale $sale): array
    {
        $sale->loadMissing(['member', 'cashier', 'items.product']);

        return [
            'id' => (int) $sale->id,
            'member_id' => $sale->member_id ? (int) $sale->member_id : null,
            'member' => $sale->member?->name,
            'user_id' => $sale->user_id ? (int) $sale->user_id : null,
            'cashier' => $sale->cashier?->name,
            'total' => (float) $sale->total,
            'payment_method' => (string) $sale->payment_method,
            'status' => $sale->status?->value,
            'note' => $sale->note,
            'items' => $sale->items->map(fn ($item): array => [
                'id' => (int) $item->id,
                'product_id' => (int) $item->product_id,
                'product_name' => $item->product?->name,
                'quantity' => (int) $item->quantity,
                'unit_price' => (float) $item->unit_price,
                'subtotal' => (float) $item->subtotal,
            ])->values()->all(),
            'created_at' => $sale->created_at?->toISOString(),
            'updated_at' => $sale->updated_at?->toISOString(),
        ];
    }
}
