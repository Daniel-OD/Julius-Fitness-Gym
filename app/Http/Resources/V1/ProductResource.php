<?php

namespace App\Http\Resources\V1;

use App\Models\Product;
use App\Services\Api\Schemas\ProductSchema;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Product */
class ProductResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    #[\Override]
    public function toArray(Request $request): array
    {
        /** @var Product $product */
        $product = $this->resource;

        return ProductSchema::resource($product);
    }
}
