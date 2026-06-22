<?php

namespace App\Filament\Resources\StockMovements\Tables;

use App\Enums\StockMovementType;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class StockMovementTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label(__('app.fields.date'))
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('product.name')
                    ->label(__('app.resources.products.singular'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('type')
                    ->badge(),
                TextColumn::make('quantity')
                    ->label(__('app.shop.quantity')),
                TextColumn::make('quantity_before')
                    ->label(__('app.shop.quantity_before')),
                TextColumn::make('quantity_after')
                    ->label(__('app.shop.quantity_after')),
                TextColumn::make('creator.name')
                    ->label(__('app.fields.created_by'))
                    ->placeholder('—'),
                TextColumn::make('note')
                    ->label(__('app.fields.note'))
                    ->limit(40)
                    ->toggleable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('type')
                    ->label(__('app.shop.movement_type'))
                    ->options(collect(StockMovementType::cases())->mapWithKeys(
                        fn (StockMovementType $type): array => [$type->value => $type->getLabel()],
                    )->all()),
                SelectFilter::make('product_id')
                    ->label(__('app.resources.products.singular'))
                    ->relationship('product', 'name')
                    ->searchable(),
                Filter::make('date')
                    ->schema([
                        DatePicker::make('from')->label(__('app.fields.date_from')),
                        DatePicker::make('to')->label(__('app.fields.date_to')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'] ?? null, fn (Builder $q, string $date): Builder => $q->whereDate('created_at', '>=', $date))
                            ->when($data['to'] ?? null, fn (Builder $q, string $date): Builder => $q->whereDate('created_at', '<=', $date));
                    }),
            ])
            ->recordActions([])
            ->toolbarActions([]);
    }
}
