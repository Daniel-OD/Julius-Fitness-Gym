<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Helpers\Helpers;
use App\Models\ProductCategory;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                TextInput::make('name')
                    ->label(__('app.fields.name'))
                    ->required()
                    ->columnSpanFull(),
                TextInput::make('code')
                    ->label(__('app.fields.code'))
                    ->maxLength(50)
                    ->unique(ignoreRecord: true),
                Select::make('category_id')
                    ->label(__('app.shop.category'))
                    ->options(fn (): array => ProductCategory::query()->orderBy('sort_order')->pluck('name', 'id')->all())
                    ->searchable()
                    ->nullable(),
                TextInput::make('price')
                    ->label(__('app.fields.price'))
                    ->numeric()
                    ->required()
                    ->prefix(fn (): string => Helpers::getCurrencySymbol()),
                TextInput::make('cost_price')
                    ->label(__('app.shop.cost_price'))
                    ->numeric()
                    ->prefix(fn (): string => Helpers::getCurrencySymbol()),
                TextInput::make('unit')
                    ->label(__('app.shop.unit'))
                    ->default('pcs')
                    ->maxLength(20),
                Textarea::make('description')
                    ->label(__('app.fields.description'))
                    ->columnSpanFull(),
                Toggle::make('is_active')
                    ->label(__('app.fields.is_active'))
                    ->default(true),
                Toggle::make('track_stock')
                    ->label(__('app.shop.track_stock'))
                    ->default(true),
                FileUpload::make('images')
                    ->label(__('app.fields.gallery_images'))
                    ->multiple()
                    ->image()
                    ->disk('public')
                    ->directory('products')
                    ->maxSize(10240)
                    ->reorderable()
                    ->appendFiles()
                    ->columnSpanFull(),
            ]);
    }
}
