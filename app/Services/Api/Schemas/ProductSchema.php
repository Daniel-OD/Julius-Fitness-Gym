<?php

namespace App\Services\Api\Schemas;

use App\Models\Product;
use Illuminate\Contracts\Validation\ValidationRule;

final class ProductSchema
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
            'searchable' => ['name', 'code', 'description'],
            'sortable' => ['id', 'created_at', 'name', 'price'],
            'default_sort' => '-id',
            'status_column' => 'is_active',
            'includes' => ['category', 'stockLevel'],
            'filters' => [
                'category_id' => ['type' => 'exact', 'column' => 'category_id'],
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
            'category_id' => ['nullable', 'integer', 'exists:product_categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:50', 'unique:products,code'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'cost_price' => ['nullable', 'numeric', 'min:0'],
            'unit' => ['nullable', 'string', 'max:20'],
            'is_active' => ['sometimes', 'boolean'],
            'track_stock' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public static function updateRules(): array
    {
        return [
            'category_id' => ['sometimes', 'nullable', 'integer', 'exists:product_categories,id'],
            'name' => ['sometimes', 'string', 'max:255'],
            'code' => ['sometimes', 'nullable', 'string', 'max:50'],
            'description' => ['sometimes', 'nullable', 'string'],
            'price' => ['sometimes', 'numeric', 'min:0'],
            'cost_price' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'unit' => ['sometimes', 'nullable', 'string', 'max:20'],
            'is_active' => ['sometimes', 'boolean'],
            'track_stock' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function resource(Product $product): array
    {
        $product->loadMissing(['category', 'stockLevel']);

        return [
            'id' => (int) $product->id,
            'category_id' => $product->category_id ? (int) $product->category_id : null,
            'category' => $product->category?->name,
            'name' => (string) $product->name,
            'code' => (string) $product->code,
            'description' => $product->description ? (string) $product->description : null,
            'price' => (float) $product->price,
            'cost_price' => $product->cost_price !== null ? (float) $product->cost_price : null,
            'unit' => (string) $product->unit,
            'is_active' => (bool) $product->is_active,
            'track_stock' => (bool) $product->track_stock,
            'stock_quantity' => $product->track_stock ? $product->currentStock() : null,
            'images' => is_array($product->images) ? $product->images : [],
            'created_at' => $product->created_at?->toISOString(),
            'updated_at' => $product->updated_at?->toISOString(),
            'deleted_at' => $product->deleted_at?->toISOString(),
        ];
    }
}
