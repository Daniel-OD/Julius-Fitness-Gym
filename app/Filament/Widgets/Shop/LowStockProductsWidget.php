<?php

namespace App\Filament\Widgets\Shop;

use App\Filament\Resources\Products\ProductResource;
use App\Helpers\Helpers;
use App\Models\Product;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class LowStockProductsWidget extends TableWidget
{
    protected static ?int $sort = -33;

    protected static ?string $heading = null;

    /**
     * @var int | string | array<string, int | null>
     */
    protected int|string|array $columnSpan = 'full';

    #[\Override]
    public function table(Table $table): Table
    {
        $threshold = (int) config('shop.low_stock_threshold', 5);

        return $table
            ->heading(__('app.shop.low_stock_heading'))
            ->description(__('app.shop.low_stock_description', ['threshold' => $threshold]))
            ->paginated(false)
            ->query(fn (): Builder => Product::query()
                ->with('stockLevel')
                ->join('stock_levels', 'products.id', '=', 'stock_levels.product_id')
                ->where('products.track_stock', true)
                ->where('products.is_active', true)
                ->where('stock_levels.quantity', '<', $threshold)
                ->orderBy('stock_levels.quantity')
                ->select('products.*'))
            ->columns([
                TextColumn::make('name')
                    ->label(__('app.fields.name'))
                    ->url(fn (Product $record): string => ProductResource::getUrl('index')),
                TextColumn::make('code')
                    ->label(__('app.fields.code')),
                TextColumn::make('stockLevel.quantity')
                    ->label(__('app.shop.stock'))
                    ->badge()
                    ->color('danger'),
                TextColumn::make('price')
                    ->label(__('app.fields.price'))
                    ->formatStateUsing(fn (?float $state): string => Helpers::formatCurrency($state)),
            ]);
    }
}
