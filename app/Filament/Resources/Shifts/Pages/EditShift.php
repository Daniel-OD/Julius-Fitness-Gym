<?php

namespace App\Filament\Resources\Shifts\Pages;

use App\Filament\Resources\Shifts\ShiftResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditShift extends EditRecord
{
    protected static string $resource = ShiftResource::class;

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
            ShiftResource::getUrl('index') => ShiftResource::getNavigationLabel(),
            (string) ($this->record->name ?? ''),
        ];
    }
}
