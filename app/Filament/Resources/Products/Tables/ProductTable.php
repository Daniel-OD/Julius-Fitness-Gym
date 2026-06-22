<?php

namespace App\Filament\Resources\Products\Tables;

use App\Enums\StockMovementType;
use App\Helpers\Helpers;
use App\Models\Product;
use App\Models\StockMovement;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ProductTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label(__('app.fields.code'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->label(__('app.fields.name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('category.name')
                    ->label(__('app.shop.category'))
                    ->toggleable(),
                TextColumn::make('price')
                    ->label(__('app.fields.price'))
                    ->formatStateUsing(fn (?float $state): string => Helpers::formatCurrency($state)),
                TextColumn::make('stockLevel.quantity')
                    ->label(__('app.shop.stock'))
                    ->badge()
                    ->color(fn (Product $record): string => $record->isLowStock() ? 'danger' : 'success')
                    ->placeholder('—')
                    ->visible(fn (): bool => true),
                IconColumn::make('is_active')
                    ->label(__('app.fields.is_active'))
                    ->boolean(),
            ])
            ->defaultSort('name')
            ->emptyStateActions([
                CreateAction::make()
                    ->label(__('app.actions.new', ['resource' => __('app.resources.products.singular')]))
                    ->hidden(fn (): bool => Product::exists()),
            ])
            ->recordActions([
                ViewAction::make()->modalWidth('2xl'),
                EditAction::make()->modalWidth('2xl'),
                Action::make('adjust_stock')
                    ->label(__('app.shop.adjust_stock'))
                    ->icon('heroicon-o-arrows-up-down')
                    ->visible(fn (Product $record): bool => $record->track_stock)
                    ->schema([
                        Select::make('type')
                            ->label(__('app.shop.movement_type'))
                            ->options([
                                StockMovementType::In->value => StockMovementType::In->getLabel(),
                                StockMovementType::Adjustment->value => StockMovementType::Adjustment->getLabel(),
                            ])
                            ->required()
                            ->native(false),
                        TextInput::make('quantity')
                            ->label(__('app.shop.quantity'))
                            ->numeric()
                            ->required()
                            ->minValue(0),
                        TextInput::make('note')
                            ->label(__('app.fields.note'))
                            ->maxLength(255),
                    ])
                    ->action(function (Product $record, array $data): void {
                        $type = StockMovementType::from($data['type']);
                        $quantity = (int) $data['quantity'];

                        StockMovement::query()->create([
                            'product_id' => $record->id,
                            'type' => $type,
                            'quantity' => $quantity,
                            'note' => $data['note'] ?? null,
                            'created_by' => auth()->id(),
                        ]);

                        Notification::make()
                            ->title(__('app.shop.stock_updated'))
                            ->success()
                            ->send();
                    }),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
