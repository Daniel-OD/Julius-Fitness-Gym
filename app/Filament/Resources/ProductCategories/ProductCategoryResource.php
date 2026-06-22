<?php

namespace App\Filament\Resources\ProductCategories;

use App\Filament\Resources\ProductCategories\Pages\ListProductCategories;
use App\Filament\Resources\ProductCategories\Schemas\ProductCategoryForm;
use App\Filament\Resources\ProductCategories\Tables\ProductCategoryTable;
use App\Models\ProductCategory;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class ProductCategoryResource extends Resource
{
    protected static ?string $model = ProductCategory::class;

    protected static ?string $recordTitleAttribute = 'name';

    #[\Override]
    public static function getModelLabel(): string
    {
        return __('app.resources.product_categories.singular');
    }

    #[\Override]
    public static function getPluralModelLabel(): string
    {
        return __('app.resources.product_categories.plural');
    }

    #[\Override]
    public static function getNavigationLabel(): string
    {
        return static::getPluralModelLabel();
    }

    #[\Override]
    public static function form(Schema $schema): Schema
    {
        return ProductCategoryForm::configure($schema);
    }

    #[\Override]
    public static function table(Table $table): Table
    {
        return ProductCategoryTable::configure($table);
    }

    #[\Override]
    public static function getPages(): array
    {
        return [
            'index' => ListProductCategories::route('/'),
        ];
    }
}
