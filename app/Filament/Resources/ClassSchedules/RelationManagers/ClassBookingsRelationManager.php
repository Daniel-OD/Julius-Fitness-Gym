<?php

namespace App\Filament\Resources\ClassSchedules\RelationManagers;

use App\Enums\BookingStatus;
use App\Models\ClassBooking;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class ClassBookingsRelationManager extends RelationManager
{
    protected static string $relationship = 'bookings';

    #[\Override]
    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('app.classes.resources.class_booking.plural');
    }

    #[\Override]
    public function isReadOnly(): bool
    {
        return false;
    }

    #[\Override]
    public function form(Schema $schema): Schema
    {
        return $schema;
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('member.name')
                    ->label(__('app.fields.member'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('booked_date')
                    ->label(__('app.classes.fields.booked_date'))
                    ->date('d-m-Y')
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->label(__('app.fields.status')),
            ])
            ->defaultSort('booked_date', 'desc')
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
                Action::make('cancel')
                    ->label(__('app.actions.cancel'))
                    ->icon('heroicon-m-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (ClassBooking $record): bool => $record->status === BookingStatus::Booked)
                    ->action(function (ClassBooking $record): void {
                        $record->update(['status' => BookingStatus::Cancelled]);
                        Notification::make()->title(__('app.classes.notifications.booking_cancelled'))->success()->send();
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateIcon('heroicon-o-ticket')
            ->emptyStateHeading(__('app.empty.no_records', ['records' => __('app.classes.resources.class_booking.plural')]));
    }
}
