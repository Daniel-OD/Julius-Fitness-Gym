<?php

namespace App\Filament\Resources\StaffProfiles\Tables;

use App\Filament\Resources\StaffProfiles\StaffProfileResource;
use App\Helpers\Helpers;
use App\Models\StaffProfile;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class StaffProfileTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label(__('app.hr.fields.user'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('employee_code')
                    ->label(__('app.hr.fields.employee_code'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('department')
                    ->label(__('app.hr.fields.department'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('position')
                    ->label(__('app.hr.fields.position'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('base_salary')
                    ->label(__('app.hr.fields.base_salary'))
                    ->formatStateUsing(fn (?float $state): string => Helpers::formatCurrency((float) $state))
                    ->sortable(),
            ])
            ->defaultSort('employee_code')
            ->emptyStateActions([
                CreateAction::make()
                    ->label(__('app.actions.new', ['resource' => StaffProfileResource::getModelLabel()]))
                    ->modalHeading(__('app.actions.new', ['resource' => StaffProfileResource::getModelLabel()]))
                    ->modalWidth('lg')
                    ->createAnother(false)
                    ->hidden(fn (): bool => StaffProfile::exists()),
            ])
            ->recordActions([
                ViewAction::make(),
                Action::make('attendanceQr')
                    ->label(__('app.hr.actions.open_qr'))
                    ->icon('heroicon-o-qr-code')
                    ->url(fn (StaffProfile $record): string => route('staff.checkin.scan', $record->attendance_token), shouldOpenInNewTab: true),
                EditAction::make()->modalWidth('lg'),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
