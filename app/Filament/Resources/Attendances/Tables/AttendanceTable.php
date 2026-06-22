<?php

namespace App\Filament\Resources\Attendances\Tables;

use App\Enums\AttendanceStatus;
use App\Filament\Resources\Attendances\AttendanceResource;
use App\Models\Attendance;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AttendanceTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label(__('app.hr.fields.user'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('date')
                    ->label(__('app.fields.date'))
                    ->date()
                    ->sortable(),
                TextColumn::make('check_in')
                    ->label(__('app.hr.fields.check_in'))
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('check_out')
                    ->label(__('app.hr.fields.check_out'))
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('method')
                    ->label(__('app.fields.method'))
                    ->badge(),
                TextColumn::make('status')
                    ->label(__('app.fields.status'))
                    ->badge()
                    ->color(fn (AttendanceStatus $state): string => $state->getColor()),
                TextColumn::make('note')
                    ->label(__('app.fields.note'))
                    ->limit(40)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('date', 'desc')
            ->filters([
                SelectFilter::make('user_id')
                    ->label(__('app.hr.fields.user'))
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),
                Filter::make('date')
                    ->schema([
                        DatePicker::make('date_from')
                            ->label(__('app.fields.date_from')),
                        DatePicker::make('date_to')
                            ->label(__('app.fields.date_to')),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when(
                            $data['date_from'] ?? null,
                            fn (Builder $query, string $date): Builder => $query->whereDate('date', '>=', $date),
                        )
                        ->when(
                            $data['date_to'] ?? null,
                            fn (Builder $query, string $date): Builder => $query->whereDate('date', '<=', $date),
                        )),
                SelectFilter::make('status')
                    ->label(__('app.fields.status'))
                    ->options(AttendanceStatus::class),
                TrashedFilter::make(),
            ])
            ->emptyStateActions([
                CreateAction::make()
                    ->label(__('app.actions.new', ['resource' => AttendanceResource::getModelLabel()]))
                    ->modalHeading(__('app.actions.new', ['resource' => AttendanceResource::getModelLabel()]))
                    ->modalWidth('lg')
                    ->createAnother(false)
                    ->hidden(fn (): bool => Attendance::exists()),
            ])
            ->recordActions([
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
