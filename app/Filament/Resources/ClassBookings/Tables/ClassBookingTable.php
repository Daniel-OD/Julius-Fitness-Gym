<?php

namespace App\Filament\Resources\ClassBookings\Tables;

use App\Enums\BookingStatus;
use App\Models\ClassBooking;
use App\Models\GymClass;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\DatePicker;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class ClassBookingTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['member', 'schedule.gymClass.instructor']))
            ->columns([
                TextColumn::make('id')->sortable()->toggleable(isToggledHiddenByDefault: true)->label(__('app.fields.id')),
                TextColumn::make('member.name')->searchable()->sortable()->label(__('app.fields.member')),
                TextColumn::make('schedule.gymClass.name')->searchable()->sortable()->label(__('app.classes.resources.gym_class.singular')),
                TextColumn::make('schedule.gymClass.instructor.name')->label(__('app.classes.fields.instructor')),
                TextColumn::make('booked_date')->date('d-m-Y')->sortable()->label(__('app.classes.fields.booked_date')),
                TextColumn::make('status')->badge()->label(__('app.fields.status')),
            ])
            ->defaultSort('booked_date', 'desc')
            ->filters([
                SelectFilter::make('gym_class')
                    ->label(__('app.classes.resources.gym_class.singular'))
                    ->options(fn (): array => GymClass::query()->pluck('name', 'id')->toArray())
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when($data['value'], fn (Builder $q, $id) => $q->whereHas(
                            'schedule',
                            fn (Builder $sq) => $sq->where('gym_class_id', $id)
                        ))),
                SelectFilter::make('instructor')
                    ->label(__('app.classes.fields.instructor'))
                    ->options(fn (): array => User::query()
                        ->whereHas('roles', fn ($q) => $q->whereIn('name', ['instructor', 'super_admin', 'owner']))
                        ->pluck('name', 'id')
                        ->toArray())
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when($data['value'], fn (Builder $q, $id) => $q->whereHas(
                            'schedule.gymClass',
                            fn (Builder $sq) => $sq->where('instructor_id', $id)
                        ))),
                SelectFilter::make('status')
                    ->label(__('app.fields.status'))
                    ->options([
                        BookingStatus::Booked->value => BookingStatus::Booked->getLabel(),
                        BookingStatus::Attended->value => BookingStatus::Attended->getLabel(),
                        BookingStatus::Cancelled->value => BookingStatus::Cancelled->getLabel(),
                    ]),
                Filter::make('date_range')
                    ->label(__('app.fields.date_range'))
                    ->form([
                        DatePicker::make('from')->label(__('app.fields.date_from')),
                        DatePicker::make('to')->label(__('app.fields.date_to')),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when($data['from'], fn (Builder $q, $d) => $q->whereDate('booked_date', '>=', $d))
                        ->when($data['to'], fn (Builder $q, $d) => $q->whereDate('booked_date', '<=', $d))),
                TrashedFilter::make(),
            ])
            ->recordActions([
                Action::make('mark_attended')
                    ->label(__('app.classes.actions.mark_attended'))
                    ->icon('heroicon-m-check-circle')
                    ->color('success')
                    ->visible(fn (ClassBooking $record): bool => $record->status === BookingStatus::Booked)
                    ->action(function (ClassBooking $record): void {
                        $record->update(['status' => BookingStatus::Attended]);
                        Notification::make()->title(__('app.classes.notifications.marked_attended'))->success()->send();
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('mark_attended_bulk')
                        ->label(__('app.classes.actions.mark_attended'))
                        ->icon('heroicon-m-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function (Collection $records): void {
                            $records->each(fn (ClassBooking $b) => $b->update(['status' => BookingStatus::Attended]));
                            Notification::make()->title(__('app.classes.notifications.marked_attended'))->success()->send();
                        }),
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateIcon('heroicon-o-ticket')
            ->emptyStateHeading(__('app.empty.no_records', ['records' => __('app.classes.resources.class_booking.plural')]));
    }
}
