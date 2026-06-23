<?php

namespace App\Filament\Resources\GymClasses;

use App\Filament\Resources\GymClasses\Pages\CreateGymClass;
use App\Filament\Resources\GymClasses\Pages\EditGymClass;
use App\Filament\Resources\GymClasses\Pages\ListGymClasses;
use App\Filament\Resources\GymClasses\RelationManagers\ClassSchedulesRelationManager;
use App\Filament\Resources\GymClasses\Schemas\GymClassForm;
use App\Filament\Resources\GymClasses\Tables\GymClassTable;
use App\Models\GymClass;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

/** @extends resource<GymClass> */
class GymClassResource extends Resource
{
    protected static ?string $model = GymClass::class;

    protected static ?string $recordTitleAttribute = 'name';

    #[\Override]
    public static function getModelLabel(): string
    {
        return __('app.classes.resources.gym_class.singular');
    }

    #[\Override]
    public static function getPluralModelLabel(): string
    {
        return __('app.classes.resources.gym_class.plural');
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
        return GymClassForm::configure($schema);
    }

    #[\Override]
    public static function table(Table $table): Table
    {
        return GymClassTable::configure($table);
    }

    #[\Override]
    public static function getRelations(): array
    {
        return [
            ClassSchedulesRelationManager::class,
        ];
    }

    #[\Override]
    public static function getPages(): array
    {
        return [
            'index' => ListGymClasses::route('/'),
            'create' => CreateGymClass::route('/create'),
            'edit' => EditGymClass::route('/{record}/edit'),
        ];
    }

    /** @return Builder<GymClass> */
    #[\Override]
    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([SoftDeletingScope::class]);
    }
}
