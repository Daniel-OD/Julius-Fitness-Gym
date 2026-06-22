<?php

namespace App\Filament\Resources\ProductCategories\Tables;

use App\Models\ProductCategory;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ProductCategoryTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('app.fields.name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('sort_order')
                    ->label(__('app.fields.sort_order'))
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label(__('app.fields.is_active'))
                    ->boolean(),
                TextColumn::make('products_count')
                    ->counts('products')
                    ->label(__('app.shop.products_count')),
            ])
            ->defaultSort('sort_order')
            ->emptyStateActions([
                CreateAction::make()
                    ->label(__('app.actions.new', ['resource' => __('app.resources.product_categories.singular')]))
                    ->modalHeading(__('app.actions.new', ['resource' => __('app.resources.product_categories.singular')]))
                    ->modalWidth('sm')
                    ->createAnother(false)
                    ->hidden(fn (): bool => ProductCategory::exists()),
            ])
            ->recordActions([
                EditAction::make()->modalWidth('sm'),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
