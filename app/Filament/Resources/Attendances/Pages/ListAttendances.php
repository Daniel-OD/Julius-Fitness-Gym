<?php

namespace App\Filament\Resources\Attendances\Pages;

use App\Filament\Resources\Attendances\AttendanceResource;
use App\Models\Attendance;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAttendances extends ListRecords
{
    protected static string $resource = AttendanceResource::class;

    #[\Override]
    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->icon('heroicon-m-plus')
                ->label(__('app.actions.new', ['resource' => AttendanceResource::getModelLabel()]))
                ->modalHeading(__('app.actions.new', ['resource' => AttendanceResource::getModelLabel()]))
                ->modalWidth('lg')
                ->createAnother(false)
                ->visible(Attendance::exists()),
        ];
    }

    #[\Override]
    public function getBreadcrumbs(): array
    {
        return [
            __('app.navigation.groups.hr'),
            AttendanceResource::getNavigationLabel(),
        ];
    }
}
