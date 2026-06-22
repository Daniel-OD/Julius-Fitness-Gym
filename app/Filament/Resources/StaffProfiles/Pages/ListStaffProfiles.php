<?php

namespace App\Filament\Resources\StaffProfiles\Pages;

use App\Filament\Resources\StaffProfiles\StaffProfileResource;
use App\Models\StaffProfile;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListStaffProfiles extends ListRecords
{
    protected static string $resource = StaffProfileResource::class;

    #[\Override]
    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->icon('heroicon-m-plus')
                ->label(__('app.actions.new', ['resource' => StaffProfileResource::getModelLabel()]))
                ->modalHeading(__('app.actions.new', ['resource' => StaffProfileResource::getModelLabel()]))
                ->modalWidth('lg')
                ->createAnother(false)
                ->visible(StaffProfile::exists()),
        ];
    }

    #[\Override]
    public function getBreadcrumbs(): array
    {
        return [
            __('app.navigation.groups.hr'),
            StaffProfileResource::getNavigationLabel(),
        ];
    }
}
