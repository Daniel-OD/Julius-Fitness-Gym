<?php

namespace App\Filament\Resources\ClassSchedules\Tables;

use App\Enums\BookingStatus;
use App\Models\ClassBooking;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class ClassScheduleTable
{
    /** @return array<int, string> */
    private static function dayLabels(): array
    {
        return [
            0 => __('app.classes.days.sunday'),
            1 => __('app.classes.days.monday'),
            2 => __('app.classes.days.tuesday'),
            3 => __('app.classes.days.wednesday'),
            4 => __('app.classes.days.thursday'),
            5 => __('app.classes.days.friday'),
            6 => __('app.classes.days.saturday'),
        ];
    }

    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('gymClass.name')
                    ->searchable()
                    ->sortable()
                    ->label(__('app.classes.resources.gym_class.singular')),
                TextColumn::make('day_of_week')
                    ->label(__('app.classes.fields.day_of_week'))
                    ->formatStateUsing(fn (int $state): string => self::dayLabels()[$state] ?? (string) $state)
                    ->sortable(),
                TextColumn::make('start_time')
                    ->label(__('app.classes.fields.start_time'))
                    ->sortable(),
                TextColumn::make('location')
                    ->label(__('app.classes.fields.location'))
                    ->placeholder('—'),
                TextColumn::make('gymClass.capacity')
                    ->label(__('app.classes.fields.capacity')),
                TextColumn::make('weekly_booked')
                    ->label(__('app.classes.fields.booked_this_week'))
                    ->state(function ($record): int {
                        return ClassBooking::query()
                            ->where('class_schedule_id', $record->id)
                            ->whereBetween('booked_date', [
                                now()->startOfWeek(0),
                                now()->endOfWeek(6),
                            ])
                            ->whereNot('status', BookingStatus::Cancelled)
                            ->count();
                    }),
                IconColumn::make('is_active')
                    ->boolean()
                    ->label(__('app.fields.is_active')),
            ])
            ->defaultSort('day_of_week')
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make()->hiddenLabel(),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateIcon('heroicon-o-clock')
            ->emptyStateHeading(__('app.empty.no_records', ['records' => __('app.classes.resources.class_schedule.plural')]));
    }
}
