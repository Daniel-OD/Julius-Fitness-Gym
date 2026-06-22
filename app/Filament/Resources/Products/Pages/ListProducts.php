<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use App\Models\Product;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;

    #[\Override]
    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->icon('heroicon-m-plus')
                ->label(__('app.actions.new', ['resource' => ProductResource::getModelLabel()]))
                ->modalHeading(__('app.actions.new', ['resource' => ProductResource::getModelLabel()]))
                ->modalWidth('2xl')
                ->createAnother(false)
                ->visible(Product::exists()),
        ];
    }

    #[\Override]
    public function getBreadcrumbs(): array
    {
        return [
            __('app.navigation.groups.billing'),
            ProductResource::getNavigationLabel(),
        ];
    }
}
