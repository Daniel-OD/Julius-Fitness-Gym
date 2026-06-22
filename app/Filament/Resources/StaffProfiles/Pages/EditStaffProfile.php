<?php

namespace App\Filament\Resources\StaffProfiles\Pages;

use App\Filament\Resources\StaffProfiles\StaffProfileResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditStaffProfile extends EditRecord
{
    protected static string $resource = StaffProfileResource::class;

    #[\Override]
    public function getTitle(): string
    {
        return __('app.actions.edit');
    }

    #[\Override]
    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }

    #[\Override]
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }

    #[\Override]
    public function getBreadcrumbs(): array
    {
        return [
            __('app.navigation.groups.hr'),
            StaffProfileResource::getUrl('index') => StaffProfileResource::getNavigationLabel(),
            (string) ($this->record->employee_code ?? ''),
        ];
    }
}
