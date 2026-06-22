<?php

namespace App\Filament\Resources\PayrollPeriods;

use App\Filament\Resources\PayrollPeriods\Pages\ListPayrollPeriods;
use App\Filament\Resources\PayrollPeriods\Pages\ViewPayrollPeriod;
use App\Filament\Resources\PayrollPeriods\RelationManagers\PayrollItemsRelationManager;
use App\Filament\Resources\PayrollPeriods\Schemas\PayrollPeriodInfolist;
use App\Filament\Resources\PayrollPeriods\Tables\PayrollPeriodTable;
use App\Models\PayrollPeriod;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class PayrollPeriodResource extends Resource
{
    protected static ?string $model = PayrollPeriod::class;

    protected static ?string $recordTitleAttribute = 'id';

    #[\Override]
    public static function getModelLabel(): string
    {
        return __('app.resources.payroll_periods.singular');
    }

    #[\Override]
    public static function getPluralModelLabel(): string
    {
        return __('app.resources.payroll_periods.plural');
    }

    #[\Override]
    public static function getNavigationLabel(): string
    {
        return static::getPluralModelLabel();
    }

    #[\Override]
    public static function canCreate(): bool
    {
        return false;
    }

    #[\Override]
    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    #[\Override]
    public static function table(Table $table): Table
    {
        return PayrollPeriodTable::configure($table);
    }

    #[\Override]
    public static function infolist(Schema $schema): Schema
    {
        return PayrollPeriodInfolist::configure($schema);
    }

    #[\Override]
    public static function getRelations(): array
    {
        return [
            PayrollItemsRelationManager::class,
        ];
    }

    #[\Override]
    public static function getPages(): array
    {
        return [
            'index' => ListPayrollPeriods::route('/'),
            'view' => ViewPayrollPeriod::route('/{record}'),
        ];
    }
}
