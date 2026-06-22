<?php

namespace App\Filament\Resources\Sales\Schemas;

use App\Helpers\Helpers;
use App\Support\Billing\PaymentMethod;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class SaleInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                TextEntry::make('created_at')->label(__('app.fields.date'))->dateTime(),
                TextEntry::make('member.name')->label(__('app.fields.member'))->placeholder(__('app.shop.walk_in')),
                TextEntry::make('cashier.name')->label(__('app.shop.cashier'))->placeholder('—'),
                TextEntry::make('payment_method')
                    ->label(__('app.fields.payment_method'))
                    ->formatStateUsing(fn (?string $state): string => PaymentMethod::channelLabel($state)),
                TextEntry::make('status')->badge(),
                TextEntry::make('total')
                    ->label(__('app.fields.total'))
                    ->formatStateUsing(fn (?float $state): string => Helpers::formatCurrency($state)),
                TextEntry::make('note')->label(__('app.fields.note'))->columnSpanFull(),
                RepeatableEntry::make('items')
                    ->label(__('app.shop.sale_items'))
                    ->columnSpanFull()
                    ->schema([
                        TextEntry::make('product.name')->label(__('app.resources.products.singular')),
                        TextEntry::make('quantity')->label(__('app.shop.quantity')),
                        TextEntry::make('unit_price')
                            ->label(__('app.fields.price'))
                            ->formatStateUsing(fn (?float $state): string => Helpers::formatCurrency($state)),
                        TextEntry::make('subtotal')
                            ->label(__('app.fields.subtotal'))
                            ->formatStateUsing(fn (?float $state): string => Helpers::formatCurrency($state)),
                    ])
                    ->columns(4),
            ]);
    }
}
