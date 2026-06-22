<?php

namespace App\Filament\Resources\Exercises\Tables;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ExerciseTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label(__('app.fields.name'))->searchable()->sortable(),
                TextColumn::make('category')->badge(),
                TextColumn::make('equipment')->label(__('app.fitness.equipment')),
                IconColumn::make('is_active')->label(__('app.fields.is_active'))->boolean(),
            ])
            ->recordActions([
                ViewAction::make()->modalWidth('2xl'),
                EditAction::make()->modalWidth('2xl'),
                DeleteAction::make(),
            ])
            ->headerActions([
                CreateAction::make()->modalWidth('2xl'),
            ]);
    }
}
