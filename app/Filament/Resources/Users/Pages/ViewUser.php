<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\Actions\ResetUserPasswordAction;
use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

/**
 * @property-read User $record
 */
class ViewUser extends ViewRecord
{
    protected static string $resource = UserResource::class;

    #[\Override]
    public function getTitle(): string
    {
        return __('app.titles.record', [
            'resource' => UserResource::getModelLabel(),
            'name' => $this->record->name,
        ]);
    }

    #[\Override]
    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
            ResetUserPasswordAction::make(),
            DeleteAction::make(),
        ];
    }

    #[\Override]
    public function getBreadcrumbs(): array
    {
        return [
            __('app.navigation.groups.administration'),
            UserResource::getUrl('index') => UserResource::getNavigationLabel(),
            $this->record->name,
        ];
    }
}
