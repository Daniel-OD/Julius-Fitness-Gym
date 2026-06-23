<?php

namespace App\Filament\Resources\ClassBookings;

use App\Filament\Resources\ClassBookings\Pages\ListClassBookings;
use App\Filament\Resources\ClassBookings\Tables\ClassBookingTable;
use App\Models\ClassBooking;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

/** @extends resource<ClassBooking> */
class ClassBookingResource extends Resource
{
    protected static ?string $model = ClassBooking::class;

    #[\Override]
    public static function getModelLabel(): string
    {
        return __('app.classes.resources.class_booking.singular');
    }

    #[\Override]
    public static function getPluralModelLabel(): string
    {
        return __('app.classes.resources.class_booking.plural');
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
        return ClassBookingTable::configure($table);
    }

    #[\Override]
    public static function getPages(): array
    {
        return [
            'index' => ListClassBookings::route('/'),
        ];
    }

    /** @return Builder<ClassBooking> */
    #[\Override]
    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([SoftDeletingScope::class]);
    }
}
