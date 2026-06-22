<?php

namespace App\Filament\Resources\Products;

use App\Filament\Resources\Products\Pages\ListProducts;
use App\Filament\Resources\Products\Schemas\ProductForm;
use App\Filament\Resources\Products\Schemas\ProductInfolist;
use App\Filament\Resources\Products\Tables\ProductTable;
use App\Helpers\Helpers;
use App\Models\Product;
use App\Support\Filament\GlobalSearchBadge;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $recordTitleAttribute = 'name';

    #[\Override]
    public static function getModelLabel(): string
    {
        return __('app.resources.products.singular');
    }

    #[\Override]
    public static function getPluralModelLabel(): string
    {
        return __('app.resources.products.plural');
    }

    #[\Override]
    public static function getNavigationLabel(): string
    {
        return static::getPluralModelLabel();
    }

    #[\Override]
    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'code', 'description'];
    }

    #[\Override]
    public static function getGlobalSearchResultDetails(Model $record): array
    {
        assert($record instanceof Product);

        return [
            __('app.fields.price') => Helpers::formatCurrency((float) $record->price),
            __('app.shop.stock') => (string) $record->currentStock(),
            __('app.fields.status') => GlobalSearchBadge::status($record->is_active ? 'active' : 'inactive'),
        ];
    }

    #[\Override]
    public static function form(Schema $schema): Schema
    {
        return ProductForm::configure($schema);
    }

    #[\Override]
    public static function table(Table $table): Table
    {
        return ProductTable::configure($table);
    }

    #[\Override]
    public static function infolist(Schema $schema): Schema
    {
        return ProductInfolist::configure($schema);
    }

    #[\Override]
    public static function getPages(): array
    {
        return [
            'index' => ListProducts::route('/'),
        ];
    }
}
