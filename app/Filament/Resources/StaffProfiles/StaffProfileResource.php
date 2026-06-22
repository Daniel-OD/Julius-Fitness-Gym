<?php

namespace App\Filament\Resources\StaffProfiles;

use App\Filament\Resources\StaffProfiles\Pages\EditStaffProfile;
use App\Filament\Resources\StaffProfiles\Pages\ListStaffProfiles;
use App\Filament\Resources\StaffProfiles\Pages\ViewStaffProfile;
use App\Filament\Resources\StaffProfiles\RelationManagers\ShiftAssignmentsRelationManager;
use App\Filament\Resources\StaffProfiles\Schemas\StaffProfileForm;
use App\Filament\Resources\StaffProfiles\Schemas\StaffProfileInfolist;
use App\Filament\Resources\StaffProfiles\Tables\StaffProfileTable;
use App\Models\StaffProfile;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class StaffProfileResource extends Resource
{
    protected static ?string $model = StaffProfile::class;

    protected static ?string $recordTitleAttribute = 'employee_code';

    #[\Override]
    public static function getModelLabel(): string
    {
        return __('app.resources.staff_profiles.singular');
    }

    #[\Override]
    public static function getPluralModelLabel(): string
    {
        return __('app.resources.staff_profiles.plural');
    }

    #[\Override]
    public static function getNavigationLabel(): string
    {
        return static::getPluralModelLabel();
    }

    #[\Override]
    public static function form(Schema $schema): Schema
    {
        return StaffProfileForm::configure($schema);
    }

    #[\Override]
    public static function infolist(Schema $schema): Schema
    {
        return StaffProfileInfolist::configure($schema);
    }

    #[\Override]
    public static function table(Table $table): Table
    {
        return StaffProfileTable::configure($table);
    }

    #[\Override]
    public static function getRelations(): array
    {
        return [
            ShiftAssignmentsRelationManager::class,
        ];
    }

    #[\Override]
    public static function getPages(): array
    {
        return [
            'index' => ListStaffProfiles::route('/'),
            'view' => ViewStaffProfile::route('/{record}'),
            'edit' => EditStaffProfile::route('/{record}/edit'),
        ];
    }
}
