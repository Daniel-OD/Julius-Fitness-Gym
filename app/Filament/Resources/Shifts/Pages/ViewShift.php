<?php

namespace App\Filament\Resources\Shifts\Pages;

use App\Filament\Resources\Shifts\ShiftResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewShift extends ViewRecord
{
    protected static string $resource = ShiftResource::class;

    #[\Override]
    public function getTitle(): string
    {
        return (string) ($this->record->name ?? ShiftResource::getModelLabel());
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
            ShiftResource::getUrl('index') => ShiftResource::getNavigationLabel(),
            (string) ($this->record->name ?? ''),
        ];
    }
}
