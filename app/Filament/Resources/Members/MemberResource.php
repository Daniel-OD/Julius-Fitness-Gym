<?php

namespace App\Filament\Resources\Members;

use App\Filament\Resources\Members\Pages\CreateMember;
use App\Filament\Resources\Members\Pages\EditMember;
use App\Filament\Resources\Members\Pages\ListMembers;
use App\Filament\Resources\Members\Pages\ViewMember;
use App\Filament\Resources\Members\RelationManagers\CheckInsRelationManager;
use App\Filament\Resources\Members\RelationManagers\SubscriptionsRelationManager;
use App\Filament\Resources\Members\Schemas\MemberForm;
use App\Filament\Resources\Members\Schemas\MemberInfolist;
use App\Filament\Resources\Members\Tables\MemberTable;
use App\Models\Member;
use App\Support\Filament\GlobalSearchBadge;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

/** @extends resource<Member> */
class MemberResource extends Resource
{
    protected static ?string $model = Member::class;

    protected static ?string $recordTitleAttribute = 'name';

    #[\Override]
    public static function getModelLabel(): string
    {
        return __('app.resources.members.singular');
    }

    #[\Override]
    public static function getPluralModelLabel(): string
    {
        return __('app.resources.members.plural');
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
            'code',
            'email',
            'contact',
        ];
    }

    #[\Override]
    public static function getGlobalSearchResultDetails(Model $record): array
    {
        /** @var Member $record */
        $details = [];

        if (filled($record->code)) {
            $details[__('app.fields.code')] = $record->code;
        }

        if (filled($record->email)) {
            $details[__('app.fields.email')] = $record->email;
        }

        if (filled($record->contact)) {
            $details[__('app.fields.contact')] = $record->contact;
        }

        if ($record->status) {
            $details[__('app.fields.status')] = GlobalSearchBadge::status($record->status);
        }

        return $details;
    }

    /**
     * Define the form schema for the resource.
     */
    #[\Override]
    public static function form(Schema $schema): Schema
    {
        return MemberForm::configure($schema);
    }

    /**
     * Get the Filament table columns for the members list view.
     */
    #[\Override]
    public static function table(Table $table): Table
    {
        return MemberTable::configure($table);
    }

    /**
     * Add infolist to the resource.
     */
    #[\Override]
    public static function infolist(Schema $schema): Schema
    {
        return MemberInfolist::configure($schema);
    }

    #[\Override]
    public static function getRelations(): array
    {
        return [
            SubscriptionsRelationManager::class,
            CheckInsRelationManager::class,
        ];
    }

    #[\Override]
    public static function getPages(): array
    {
        return [
            'index' => ListMembers::route('/'),
            'create' => CreateMember::route('/create'),
            'edit' => EditMember::route('/{record}/edit'),
            'view' => ViewMember::route('/{record}'),
        ];
    }

    /**
     * @return Builder<Member>
     */
    #[\Override]
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
