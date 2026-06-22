<?php

namespace App\Filament\Resources\ProductCategories\Pages;

use App\Filament\Resources\ProductCategories\ProductCategoryResource;
use App\Models\ProductCategory;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListProductCategories extends ListRecords
{
    protected static string $resource = ProductCategoryResource::class;

    #[\Override]
    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->icon('heroicon-m-plus')
                ->label(__('app.actions.new', ['resource' => ProductCategoryResource::getModelLabel()]))
                ->modalHeading(__('app.actions.new', ['resource' => ProductCategoryResource::getModelLabel()]))
                ->modalWidth('sm')
                ->createAnother(false)
                ->visible(ProductCategory::exists()),
        ];
    }

    #[\Override]
    public function getBreadcrumbs(): array
    {
        return [
            __('app.navigation.groups.billing'),
            ProductCategoryResource::getNavigationLabel(),
        ];
    }
}
