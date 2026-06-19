<?php

namespace App\Filament\Resources\Services;

use App\Filament\Resources\Services\Pages\ListServices;
use App\Filament\Resources\Services\Schemas\ServiceForm;
use App\Filament\Resources\Services\Schemas\ServiceInfolist;
use App\Filament\Resources\Services\Tables\ServiceTable;
use App\Models\Service;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class ServiceResource extends Resource
{
    protected static ?string $model = Service::class;

    protected static ?string $recordTitleAttribute = 'name';

    #[\Override]
    public static function getModelLabel(): string
    {
        return __('app.resources.services.singular');
    }

    #[\Override]
    public static function getPluralModelLabel(): string
    {
        return __('app.resources.services.plural');
    }

    #[\Override]
    public static function getNavigationLabel(): string
    {
        return static::getPluralModelLabel();
    }

    #[\Override]
    public static function getGloballySearchableAttributes(): array
    {
        return [
            'name',
            'description',
        ];
    }

    #[\Override]
    public static function getGlobalSearchResultDetails(Model $record): array
    {
        /** @var Service $record */
        if (blank($record->description)) {
            return [];
        }

        return [
            __('app.fields.description') => $record->description,
        ];
    }

    #[\Override]
    public static function form(Schema $schema): Schema
    {
        return ServiceForm::configure($schema);
    }

    #[\Override]
    public static function table(Table $table): Table
    {
        return ServiceTable::configure($table);
    }

    /**
     * Add infolist to the resource.
     */
    #[\Override]
    public static function infolist(Schema $schema): Schema
    {
        return ServiceInfolist::configure($schema);
    }

    #[\Override]
    public static function getPages(): array
    {
        return [
            'index' => ListServices::route('/'),
        ];
    }
}
