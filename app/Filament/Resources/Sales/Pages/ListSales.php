<?php

namespace App\Filament\Resources\Sales\Pages;

use App\Enums\SaleStatus;
use App\Filament\Resources\Sales\SaleResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListSales extends ListRecords
{
    protected static string $resource = SaleResource::class;

    #[\Override]
    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label(__('app.shop.new_sale'))
                ->icon('heroicon-m-plus')
                ->url(SaleResource::getUrl('create')),
        ];
    }

    #[\Override]
    public function getBreadcrumbs(): array
    {
        return [
            __('app.navigation.groups.billing'),
            SaleResource::getNavigationLabel(),
        ];
    }

    #[\Override]
    public function getTabs(): array
    {
        return [
            'all' => Tab::make(__('app.common.all')),
            'completed' => Tab::make(SaleStatus::Completed->getLabel())
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('status', SaleStatus::Completed)),
            'cancelled' => Tab::make(SaleStatus::Cancelled->getLabel())
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('status', SaleStatus::Cancelled)),
        ];
    }
}
