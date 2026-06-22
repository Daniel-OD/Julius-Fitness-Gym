<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\V1\ProductResource;
use App\Models\Product;
use App\Services\Api\QueryFilters;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProductsController extends ApiController
{
    private const string RESOURCE_KEY = 'products';

    public function index(Request $request): AnonymousResourceCollection
    {
        $this->requirePermission($request, 'ViewAny:Product');

        $query = Product::query()->with(['category', 'stockLevel']);

        QueryFilters::applyIndexFilters($query, $request, self::RESOURCE_KEY);

        $perPage = QueryFilters::perPage($request->query('per_page'));

        return ProductResource::collection($query->paginate($perPage));
    }

    public function show(Request $request, Product $product): ProductResource
    {
        $this->requirePermission($request, 'View:Product');

        $product->load(['category', 'stockLevel']);

        return new ProductResource($product);
    }
}
