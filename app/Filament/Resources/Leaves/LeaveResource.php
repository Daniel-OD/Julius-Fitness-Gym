<?php

namespace App\Filament\Resources\Leaves;

use App\Filament\Resources\Leaves\Pages\ListLeaves;
use App\Filament\Resources\Leaves\Schemas\LeaveForm;
use App\Filament\Resources\Leaves\Tables\LeaveTable;
use App\Models\Leave;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class LeaveResource extends Resource
{
    protected static ?string $model = Leave::class;

    protected static ?string $recordTitleAttribute = 'id';

    #[\Override]
    public static function getModelLabel(): string
    {
        return __('app.resources.leaves.singular');
    }

    #[\Override]
    public static function getPluralModelLabel(): string
    {
        return __('app.resources.leaves.plural');
    }

    #[\Override]
    public static function getNavigationLabel(): string
    {
        return static::getPluralModelLabel();
    }

    #[\Override]
    public static function form(Schema $schema): Schema
    {
        return LeaveForm::configure($schema);
    }

    #[\Override]
    public static function table(Table $table): Table
    {
        return LeaveTable::configure($table);
    }

    #[\Override]
    public static function getPages(): array
    {
        return [
            'index' => ListLeaves::route('/'),
        ];
    }
}
