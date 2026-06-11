<?php

namespace App\Filament\Resources\Members\RelationManagers;

use App\Filament\Resources\CheckIns\CheckInResource;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class CheckInsRelationManager extends RelationManager
{
    protected static string $relationship = 'checkIns';

    protected static ?string $title = null;

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('app.checkins.plural');
    }

    public function isReadOnly(): bool
    {
        return false;
    }

    public function form(Schema $schema): Schema
    {
        return CheckInResource::form($schema);
    }

    /**
     * Reuse the check-in table (status badge, date filters, CSV export),
     * automatically scoped to the member being viewed.
     */
    public function table(Table $table): Table
    {
        return CheckInResource::table($table);
    }
}
