<?php

namespace App\Filament\Resources\StaffProfiles\Pages;

use App\Filament\Resources\StaffProfiles\StaffProfileResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewStaffProfile extends ViewRecord
{
    protected static string $resource = StaffProfileResource::class;

    #[\Override]
    public function getTitle(): string
    {
        return (string) ($this->record->employee_code ?? StaffProfileResource::getModelLabel());
    }

    #[\Override]
    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
            DeleteAction::make(),
        ];
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
