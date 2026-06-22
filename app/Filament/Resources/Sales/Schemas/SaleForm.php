<?php

namespace App\Filament\Resources\Sales\Schemas;

use App\Helpers\Helpers;
use App\Models\Member;
use App\Models\Product;
use App\Support\Billing\PaymentMethod;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class SaleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Select::make('member_id')
                    ->label(__('app.fields.member'))
                    ->options(fn (): array => Member::query()->orderBy('name')->pluck('name', 'id')->all())
                    ->searchable()
                    ->nullable(),
                Select::make('payment_method')
                    ->label(__('app.fields.payment_method'))
                    ->options(PaymentMethod::options())
                    ->default('cash')
                    ->required()
                    ->native(false),
                Repeater::make('items')
                    ->label(__('app.shop.sale_items'))
                    ->schema([
                        Select::make('product_id')
                            ->label(__('app.resources.products.singular'))
                            ->options(fn (): array => Product::query()
                                ->where('is_active', true)
                                ->orderBy('name')
                                ->pluck('name', 'id')
                                ->all())
                            ->searchable()
                            ->required()
                            ->live(),
                        TextInput::make('quantity')
                            ->label(__('app.shop.quantity'))
                            ->numeric()
                            ->default(1)
                            ->minValue(1)
                            ->required(),
                    ])
                    ->minItems(1)
                    ->defaultItems(1)
                    ->addActionLabel(__('app.shop.add_item'))
                    ->columns(2),
                TextInput::make('total_preview')
                    ->label(__('app.fields.total'))
                    ->disabled()
                    ->dehydrated(false)
                    ->prefix(fn (): string => Helpers::getCurrencySymbol())
                    ->formatStateUsing(fn (Get $get): string => self::calculateTotalPreview($get)),
                Textarea::make('note')
                    ->label(__('app.fields.note'))
                    ->rows(2),
            ]);
    }

    public static function calculateTotalPreview(Get $get): string
    {
        $items = $get('items') ?? [];
        $total = 0.0;

        foreach ($items as $item) {
            $productId = (int) ($item['product_id'] ?? 0);
            $quantity = max(1, (int) ($item['quantity'] ?? 1));

            if ($productId <= 0) {
                continue;
            }

            $price = (float) (Product::query()->whereKey($productId)->value('price') ?? 0);
            $total += $price * $quantity;
        }

        return number_format($total, 2, '.', '');
    }
}
