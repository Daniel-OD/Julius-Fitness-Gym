<?php

namespace App\Filament\Resources\StockMovements\Pages;

use App\Filament\Resources\StockMovements\StockMovementResource;
use Filament\Resources\Pages\ListRecords;

class ListStockMovements extends ListRecords
{
    protected static string $resource = StockMovementResource::class;

    #[\Override]
    public function getBreadcrumbs(): array
    {
        return [
            __('app.navigation.groups.billing'),
            StockMovementResource::getNavigationLabel(),
        ];
    }
}
