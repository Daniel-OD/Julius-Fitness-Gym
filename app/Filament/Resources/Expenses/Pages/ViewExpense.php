<?php

namespace App\Filament\Resources\Expenses\Pages;

use App\Filament\Resources\Expenses\ExpenseResource;
use Filament\Resources\Pages\ViewRecord;

class ViewExpense extends ViewRecord
{
    protected static string $resource = ExpenseResource::class;

    #[\Override]
    public function getTitle(): string
    {
        return ExpenseResource::getModelLabel();
    }

    #[\Override]
    public function getBreadcrumbs(): array
    {
        return [
            __('app.navigation.groups.billing'),
            ExpenseResource::getUrl('index') => ExpenseResource::getNavigationLabel(),
            __('app.actions.view', ['resource' => ExpenseResource::getModelLabel()]),
        ];
    }
}
