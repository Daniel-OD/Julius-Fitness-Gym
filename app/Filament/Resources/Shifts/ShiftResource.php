<?php

namespace App\Filament\Resources\Shifts;

use App\Filament\Resources\Shifts\Pages\EditShift;
use App\Filament\Resources\Shifts\Pages\ListShifts;
use App\Filament\Resources\Shifts\Pages\ViewShift;
use App\Filament\Resources\Shifts\RelationManagers\AssignedStaffRelationManager;
use App\Filament\Resources\Shifts\Schemas\ShiftForm;
use App\Filament\Resources\Shifts\Schemas\ShiftInfolist;
use App\Filament\Resources\Shifts\Tables\ShiftTable;
use App\Models\Shift;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class ShiftResource extends Resource
{
    protected static ?string $model = Shift::class;

    protected static ?string $recordTitleAttribute = 'name';

    #[\Override]
    public static function getModelLabel(): string
    {
        return __('app.resources.shifts.singular');
    }

    #[\Override]
    public static function getPluralModelLabel(): string
    {
        return __('app.resources.shifts.plural');
    }

    #[\Override]
    public static function getNavigationLabel(): string
    {
        return static::getPluralModelLabel();
    }

    #[\Override]
    public static function form(Schema $schema): Schema
    {
        return ShiftForm::configure($schema);
    }

    #[\Override]
    public static function infolist(Schema $schema): Schema
    {
        return ShiftInfolist::configure($schema);
    }

    #[\Override]
    public static function table(Table $table): Table
    {
        return ShiftTable::configure($table);
    }

    #[\Override]
    public static function getRelations(): array
    {
        return [
            AssignedStaffRelationManager::class,
        ];
    }

    #[\Override]
    public static function getPages(): array
    {
        return [
            'index' => ListShifts::route('/'),
            'view' => ViewShift::route('/{record}'),
            'edit' => EditShift::route('/{record}/edit'),
        ];
    }
}
