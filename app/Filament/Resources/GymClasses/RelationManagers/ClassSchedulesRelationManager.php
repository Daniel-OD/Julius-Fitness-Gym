<?php

namespace App\Filament\Resources\GymClasses\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class ClassSchedulesRelationManager extends RelationManager
{
    protected static string $relationship = 'schedules';

    #[\Override]
    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('app.classes.resources.class_schedule.plural');
    }

    #[\Override]
    public function isReadOnly(): bool
    {
        return false;
    }

    /** @return array<int, string> */
    private static function dayOptions(): array
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

    #[\Override]
    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('day_of_week')
                ->label(__('app.classes.fields.day_of_week'))
                ->options(self::dayOptions())
                ->required(),
            TimePicker::make('start_time')
                ->label(__('app.classes.fields.start_time'))
                ->required()
                ->seconds(false),
            TextInput::make('location')
                ->label(__('app.classes.fields.location'))
                ->maxLength(255),
            Toggle::make('is_active')
                ->label(__('app.fields.is_active'))
                ->default(true),
        ])->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('day_of_week')
                    ->label(__('app.classes.fields.day_of_week'))
                    ->formatStateUsing(fn (int $state): string => self::dayOptions()[$state] ?? (string) $state)
                    ->sortable(),
                TextColumn::make('start_time')
                    ->label(__('app.classes.fields.start_time'))
                    ->sortable(),
                TextColumn::make('location')
                    ->label(__('app.classes.fields.location'))
                    ->placeholder('—'),
                IconColumn::make('is_active')
                    ->boolean()
                    ->label(__('app.fields.is_active')),
            ])
            ->headerActions([
                CreateAction::make()->createAnother(false),
            ])
            ->recordActions([
                EditAction::make()->hiddenLabel(),
                DeleteAction::make()->hiddenLabel(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([DeleteBulkAction::make()]),
            ])
            ->emptyStateIcon('heroicon-o-clock')
            ->emptyStateHeading(__('app.empty.no_records', ['records' => __('app.classes.resources.class_schedule.plural')]));
    }
}
