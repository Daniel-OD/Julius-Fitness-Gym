<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Helpers\Helpers;
use App\Models\Product;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ProductInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                TextEntry::make('code')->label(__('app.fields.code')),
                TextEntry::make('name')->label(__('app.fields.name')),
                TextEntry::make('category.name')->label(__('app.shop.category')),
                TextEntry::make('price')
                    ->label(__('app.fields.price'))
                    ->formatStateUsing(fn (?float $state): string => Helpers::formatCurrency($state)),
                TextEntry::make('cost_price')
                    ->label(__('app.shop.cost_price'))
                    ->formatStateUsing(fn (?float $state): string => Helpers::formatCurrency($state)),
                TextEntry::make('stockLevel.quantity')
                    ->label(__('app.shop.stock'))
                    ->badge()
                    ->color(fn (Product $record): string => $record->isLowStock() ? 'danger' : 'success'),
                TextEntry::make('unit')->label(__('app.shop.unit')),
                TextEntry::make('description')->label(__('app.fields.description'))->columnSpanFull(),
                IconEntry::make('is_active')->label(__('app.fields.is_active'))->boolean(),
                IconEntry::make('track_stock')->label(__('app.shop.track_stock'))->boolean(),
            ]);
    }
}
