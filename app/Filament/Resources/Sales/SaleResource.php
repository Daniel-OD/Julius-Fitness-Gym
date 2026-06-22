<?php

namespace App\Filament\Resources\Sales;

use App\Filament\Resources\Sales\Pages\CreateSale;
use App\Filament\Resources\Sales\Pages\ListSales;
use App\Filament\Resources\Sales\Schemas\SaleForm;
use App\Filament\Resources\Sales\Schemas\SaleInfolist;
use App\Filament\Resources\Sales\Tables\SaleTable;
use App\Helpers\Helpers;
use App\Models\Sale;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class SaleResource extends Resource
{
    protected static ?string $model = Sale::class;

    protected static ?string $recordTitleAttribute = 'id';

    #[\Override]
    public static function getModelLabel(): string
    {
        return __('app.resources.sales.singular');
    }

    #[\Override]
    public static function getPluralModelLabel(): string
    {
        return __('app.resources.sales.plural');
    }

    #[\Override]
    public static function getNavigationLabel(): string
    {
        return static::getPluralModelLabel();
    }

    #[\Override]
    public static function getGloballySearchableAttributes(): array
    {
        return ['member.name', 'member.code', 'note'];
    }

    #[\Override]
    public static function getGlobalSearchResultDetails(Model $record): array
    {
        assert($record instanceof Sale);

        return [
            __('app.fields.total') => Helpers::formatCurrency((float) $record->total),
            __('app.fields.status') => $record->status?->getLabel() ?? '—',
        ];
    }

    #[\Override]
    public static function form(Schema $schema): Schema
    {
        return SaleForm::configure($schema);
    }

    #[\Override]
    public static function table(Table $table): Table
    {
        return SaleTable::configure($table);
    }

    #[\Override]
    public static function infolist(Schema $schema): Schema
    {
        return SaleInfolist::configure($schema);
    }

    #[\Override]
    public static function getPages(): array
    {
        return [
            'index' => ListSales::route('/'),
            'create' => CreateSale::route('/create'),
        ];
    }
}
