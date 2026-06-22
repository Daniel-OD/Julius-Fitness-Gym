<?php

namespace App\Filament\Resources\WorkoutTemplates\Pages;

use App\Filament\Resources\WorkoutTemplates\WorkoutTemplateResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListWorkoutTemplates extends ListRecords
{
    protected static string $resource = WorkoutTemplateResource::class;

    #[\Override]
    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label(__('app.actions.new', ['resource' => WorkoutTemplateResource::getModelLabel()])),
        ];
    }

    #[\Override]
    public function getBreadcrumbs(): array
    {
        return [__('app.navigation.groups.fitness'), WorkoutTemplateResource::getNavigationLabel()];
    }
}
