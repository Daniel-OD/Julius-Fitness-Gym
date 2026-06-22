<?php

namespace App\Http\Resources\V1;

use App\Models\Sale;
use App\Services\Api\Schemas\SaleSchema;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Sale */
class SaleResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    #[\Override]
    public function toArray(Request $request): array
    {
        /** @var Sale $sale */
        $sale = $this->resource;

        return SaleSchema::resource($sale);
    }
}
