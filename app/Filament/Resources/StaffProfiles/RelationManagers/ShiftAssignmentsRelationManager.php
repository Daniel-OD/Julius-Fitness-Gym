<?php

namespace App\Filament\Resources\StaffProfiles\RelationManagers;

use App\Models\Shift;
use App\Models\ShiftAssignment;
use Carbon\Carbon;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class ShiftAssignmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'shiftAssignments';

    protected static ?string $title = null;

    #[\Override]
    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('app.hr.shift_assignments.plural');
    }

    #[\Override]
    public function isReadOnly(): bool
    {
        return false;
    }

    #[\Override]
    public function form(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Select::make('shift_id')
                    ->label(__('app.hr.shift_assignments.fields.shift'))
                    ->options(fn (): array => Shift::query()->pluck('name', 'id')->all())
                    ->searchable()
                    ->required()
                    ->columnSpanFull(),
                DatePicker::make('valid_from')
                    ->label(__('app.hr.shift_assignments.fields.valid_from'))
                    ->native(false)
                    ->required(),
                DatePicker::make('valid_until')
                    ->label(__('app.hr.shift_assignments.fields.valid_until'))
                    ->native(false),
                Textarea::make('notes')
                    ->label(__('app.hr.shift_assignments.fields.notes'))
                    ->rows(3)
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('valid_from', 'desc')
            ->columns([
                TextColumn::make('shift.name')
                    ->label(__('app.hr.shift_assignments.fields.shift')),
                TextColumn::make('valid_from')
                    ->label(__('app.hr.shift_assignments.fields.valid_from'))
                    ->date(),
                TextColumn::make('valid_until')
                    ->label(__('app.hr.shift_assignments.fields.valid_until'))
                    ->date()
                    ->placeholder('—'),
                TextColumn::make('notes')
                    ->label(__('app.hr.shift_assignments.fields.notes'))
                    ->limit(40)
                    ->placeholder('—'),
                TextColumn::make('assignment_status')
                    ->label(__('app.hr.shift_assignments.fields.status'))
                    ->badge()
                    ->state(fn (ShiftAssignment $record): string => self::resolveStatus($record))
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'upcoming' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => __("app.hr.shift_assignments.status.{$state}")),
            ])
            ->headerActions([
                CreateAction::make()
                    ->icon('heroicon-s-plus')
                    ->modalHeading(__('app.actions.new'))
                    ->createAnother(false),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    private static function resolveStatus(ShiftAssignment $record): string
    {
        $today = Carbon::today();

        if ($today->lt($record->valid_from)) {
            return 'upcoming';
        }

        if ($record->valid_until !== null && $today->gt($record->valid_until)) {
            return 'expired';
        }

        return 'active';
    }
}
