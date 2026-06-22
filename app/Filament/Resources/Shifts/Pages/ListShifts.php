<?php

namespace App\Filament\Resources\Shifts\Pages;

use App\Filament\Resources\Shifts\ShiftResource;
use App\Models\Shift;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListShifts extends ListRecords
{
    protected static string $resource = ShiftResource::class;

    #[\Override]
    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->icon('heroicon-m-plus')
                ->label(__('app.actions.new', ['resource' => ShiftResource::getModelLabel()]))
                ->modalHeading(__('app.actions.new', ['resource' => ShiftResource::getModelLabel()]))
                ->modalWidth('md')
                ->createAnother(false)
                ->visible(Shift::exists()),
        ];
    }

    #[\Override]
    public function getBreadcrumbs(): array
    {
        return [
            __('app.navigation.groups.hr'),
            ShiftResource::getNavigationLabel(),
        ];
    }
}
