<?php

namespace App\Filament\Resources\PayrollPeriods\RelationManagers;

use App\Helpers\Helpers;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class PayrollItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    protected static ?string $title = null;

    #[\Override]
    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('app.hr.payroll.items');
    }

    #[\Override]
    public function isReadOnly(): bool
    {
        return true;
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label(__('app.hr.fields.user'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('base_salary')
                    ->label(__('app.hr.fields.base_salary'))
                    ->formatStateUsing(fn (?float $state): string => Helpers::formatCurrency((float) $state))
                    ->sortable(),
                TextColumn::make('working_days')
                    ->label(__('app.hr.fields.working_days'))
                    ->sortable(),
                TextColumn::make('present_days')
                    ->label(__('app.hr.fields.present_days'))
                    ->sortable(),
                TextColumn::make('overtime_hours')
                    ->label(__('app.hr.fields.overtime_hours'))
                    ->numeric(decimalPlaces: 2)
                    ->sortable(),
                TextColumn::make('gross')
                    ->label(__('app.hr.fields.gross'))
                    ->formatStateUsing(fn (?float $state): string => Helpers::formatCurrency((float) $state))
                    ->sortable(),
                TextColumn::make('net')
                    ->label(__('app.hr.fields.net'))
                    ->formatStateUsing(fn (?float $state): string => Helpers::formatCurrency((float) $state))
                    ->sortable(),
                TextColumn::make('status')
                    ->label(__('app.fields.status'))
                    ->badge(),
            ])
            ->defaultSort('user.name')
            ->headerActions([])
            ->recordActions([])
            ->toolbarActions([]);
    }
}
