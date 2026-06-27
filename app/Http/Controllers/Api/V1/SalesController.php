<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\SaleStatus;
use App\Http\Requests\Api\V1\SaleStoreRequest;
use App\Http\Resources\V1\SaleResource;
use App\Models\Sale;
use App\Services\Api\QueryFilters;
use App\Services\Shop\SaleService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

class SalesController extends ApiController
{
    private const string RESOURCE_KEY = 'sales';

    public function __construct(private readonly SaleService $saleService) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $this->requirePermission($request, 'ViewAny:Sale');

        $query = Sale::query()->with(['member', 'cashier', 'items.product']);

        QueryFilters::applyIndexFilters($query, $request, self::RESOURCE_KEY);

        $perPage = QueryFilters::perPage($request->query('per_page'));

        return SaleResource::collection($query->paginate($perPage));
    }

    public function store(SaleStoreRequest $request): SaleResource
    {
        $this->requirePermission($request, 'Create:Sale');

        try {
            $sale = $this->saleService->create([
                ...$request->validated(),
                'status' => SaleStatus::Completed->value,
            ], $this->currentUser($request));
        } catch (InvalidArgumentException $exception) {
            throw ValidationException::withMessages([
                'items' => [$exception->getMessage()],
            ]);
        }

        return new SaleResource($sale);
    }

    public function show(Request $request, Sale $sale): SaleResource
    {
        $this->requirePermission($request, 'View:Sale');

        $sale->load(['member', 'cashier', 'items.product']);

        return new SaleResource($sale);
    }
}
