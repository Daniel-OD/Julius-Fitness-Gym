<?php

namespace App\Filament\Resources\GymClasses\Tables;

use App\Models\GymClass;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class GymClassTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->sortable()->toggleable(isToggledHiddenByDefault: true)->label(__('app.fields.id')),
                ViewColumn::make('color')
                    ->label(__('app.classes.fields.color'))
                    ->view('filament.tables.columns.color-swatch'),
                TextColumn::make('name')->searchable()->sortable()->label(__('app.fields.name')),
                TextColumn::make('instructor.name')->searchable()->sortable()->label(__('app.classes.fields.instructor')),
                TextColumn::make('capacity')->sortable()->label(__('app.classes.fields.capacity')),
                TextColumn::make('duration_minutes')
                    ->sortable()
                    ->suffix(' min')
                    ->label(__('app.classes.fields.duration_minutes')),
                TextColumn::make('schedules_count')
                    ->counts('schedules')
                    ->label(__('app.classes.fields.schedules_count')),
                IconColumn::make('is_active')
                    ->boolean()
                    ->label(__('app.fields.is_active')),
            ])
            ->defaultSort('id', 'desc')
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make()->hiddenLabel(),
                    DeleteAction::make()->hiddenLabel(),
                    RestoreAction::make()->hiddenLabel(),
                ]),
            ])
            ->emptyStateIcon('heroicon-o-calendar')
            ->emptyStateHeading(__('app.empty.no_records', ['records' => __('app.classes.resources.gym_class.plural')]))
            ->emptyStateActions([
                CreateAction::make()
                    ->icon('heroicon-o-plus')
                    ->label(__('app.actions.new', ['resource' => __('app.classes.resources.gym_class.singular')]))
                    ->hidden(fn (): bool => GymClass::exists()),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
