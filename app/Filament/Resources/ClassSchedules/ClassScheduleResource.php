<?php

namespace App\Filament\Resources\ClassSchedules;

use App\Filament\Resources\ClassSchedules\Pages\ListClassSchedules;
use App\Filament\Resources\ClassSchedules\RelationManagers\ClassBookingsRelationManager;
use App\Filament\Resources\ClassSchedules\Tables\ClassScheduleTable;
use App\Models\ClassSchedule;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

/** @extends resource<ClassSchedule> */
class ClassScheduleResource extends Resource
{
    protected static ?string $model = ClassSchedule::class;

    #[\Override]
    public static function getModelLabel(): string
    {
        return __('app.classes.resources.class_schedule.singular');
    }

    #[\Override]
    public static function getPluralModelLabel(): string
    {
        return __('app.classes.resources.class_schedule.plural');
    }

    #[\Override]
    public static function getNavigationLabel(): string
    {
        return static::getPluralModelLabel();
    }

    #[\Override]
    public static function getNavigationGroup(): string
    {
        return __('app.navigation.groups.classes');
    }

    #[\Override]
    public static function form(Schema $schema): Schema
    {
        return $schema;
    }

    #[\Override]
    public static function table(Table $table): Table
    {
        return ClassScheduleTable::configure($table);
    }

    #[\Override]
    public static function getRelations(): array
    {
        return [
            ClassBookingsRelationManager::class,
        ];
    }

    #[\Override]
    public static function getPages(): array
    {
        return [
            'index' => ListClassSchedules::route('/'),
            'view' => Pages\ViewClassSchedule::route('/{record}'),
        ];
    }

    /** @return Builder<ClassSchedule> */
    #[\Override]
    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([SoftDeletingScope::class]);
    }
}
