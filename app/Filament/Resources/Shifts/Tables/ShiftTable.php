<?php

namespace App\Filament\Resources\Shifts\Tables;

use App\Filament\Resources\Shifts\Schemas\ShiftForm;
use App\Filament\Resources\Shifts\ShiftResource;
use App\Models\Shift;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class ShiftTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('app.fields.name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('start_time')
                    ->label(__('app.hr.fields.start_time'))
                    ->time('H:i')
                    ->sortable(),
                TextColumn::make('end_time')
                    ->label(__('app.hr.fields.end_time'))
                    ->time('H:i')
                    ->sortable(),
                TextColumn::make('days_of_week')
                    ->label(__('app.hr.fields.days_of_week'))
                    ->formatStateUsing(function (?array $state): string {
                        if (! is_array($state) || $state === []) {
                            return __('app.placeholders.dash');
                        }

                        return collect($state)
                            ->map(fn ($day): string => ShiftForm::daysOfWeekOptions()[(string) $day] ?? (string) $day)
                            ->implode(', ');
                    })
                    ->wrap(),
                ColorColumn::make('color')
                    ->label(__('app.hr.fields.color')),
                IconColumn::make('is_active')
                    ->label(__('app.fields.is_active'))
                    ->boolean(),
            ])
            ->defaultSort('name')
            ->filters([
                TrashedFilter::make(),
            ])
            ->emptyStateActions([
                CreateAction::make()
                    ->label(__('app.actions.new', ['resource' => ShiftResource::getModelLabel()]))
                    ->modalHeading(__('app.actions.new', ['resource' => ShiftResource::getModelLabel()]))
                    ->modalWidth('md')
                    ->createAnother(false)
                    ->hidden(fn (): bool => Shift::exists()),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make()->modalWidth('md'),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
