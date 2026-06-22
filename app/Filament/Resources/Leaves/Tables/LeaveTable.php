<?php

namespace App\Filament\Resources\Leaves\Tables;

use App\Enums\LeaveStatus;
use App\Filament\Resources\Leaves\LeaveResource;
use App\Models\Leave;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class LeaveTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label(__('app.hr.fields.user'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('type')
                    ->label(__('app.fields.type'))
                    ->badge(),
                TextColumn::make('start_date')
                    ->label(__('app.hr.fields.start_date'))
                    ->date()
                    ->sortable(),
                TextColumn::make('end_date')
                    ->label(__('app.hr.fields.end_date'))
                    ->date()
                    ->sortable(),
                TextColumn::make('days')
                    ->label(__('app.hr.fields.days'))
                    ->sortable(),
                TextColumn::make('status')
                    ->label(__('app.fields.status'))
                    ->badge()
                    ->color(fn (LeaveStatus $state): string => $state->getColor()),
                TextColumn::make('approver.name')
                    ->label(__('app.hr.fields.approved_by'))
                    ->placeholder(__('app.placeholders.dash'))
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('approved_at')
                    ->label(__('app.hr.fields.approved_at'))
                    ->dateTime()
                    ->placeholder(__('app.placeholders.dash'))
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('start_date', 'desc')
            ->filters([
                TrashedFilter::make(),
            ])
            ->emptyStateActions([
                CreateAction::make()
                    ->label(__('app.actions.new', ['resource' => LeaveResource::getModelLabel()]))
                    ->modalHeading(__('app.actions.new', ['resource' => LeaveResource::getModelLabel()]))
                    ->modalWidth('lg')
                    ->createAnother(false)
                    ->hidden(fn (): bool => Leave::exists()),
            ])
            ->recordActions([
                Action::make('approve')
                    ->label(__('app.hr.actions.approve'))
                    ->icon('heroicon-m-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (Leave $record): bool => $record->status === LeaveStatus::Pending)
                    ->action(function (Leave $record): void {
                        $record->update([
                            'status' => LeaveStatus::Approved,
                            'approved_by' => auth()->id(),
                            'approved_at' => now(),
                        ]);

                        Notification::make()
                            ->title(__('app.hr.notifications.leave_approved'))
                            ->success()
                            ->send();
                    }),
                Action::make('reject')
                    ->label(__('app.hr.actions.reject'))
                    ->icon('heroicon-m-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (Leave $record): bool => $record->status === LeaveStatus::Pending)
                    ->action(function (Leave $record): void {
                        $record->update([
                            'status' => LeaveStatus::Rejected,
                            'approved_by' => auth()->id(),
                            'approved_at' => now(),
                        ]);

                        Notification::make()
                            ->title(__('app.hr.notifications.leave_rejected'))
                            ->success()
                            ->send();
                    }),
                EditAction::make()->modalWidth('lg'),
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
