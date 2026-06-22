<?php

namespace App\Filament\Resources\Leaves\Pages;

use App\Filament\Resources\Leaves\LeaveResource;
use App\Models\Leave;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListLeaves extends ListRecords
{
    protected static string $resource = LeaveResource::class;

    #[\Override]
    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->icon('heroicon-m-plus')
                ->label(__('app.actions.new', ['resource' => LeaveResource::getModelLabel()]))
                ->modalHeading(__('app.actions.new', ['resource' => LeaveResource::getModelLabel()]))
                ->modalWidth('lg')
                ->createAnother(false)
                ->visible(Leave::exists()),
        ];
    }

    #[\Override]
    public function getBreadcrumbs(): array
    {
        return [
            __('app.navigation.groups.hr'),
            LeaveResource::getNavigationLabel(),
        ];
    }
}
